<?php

namespace App\Actions;

use App\Models\HubspotSubmissions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ProcessHubspotSubmissionFromDatabase
{
    public static function handle()
    {
        $hubspotapikey = config('services.azure.HubspotAPIKey');

        // \Log::info("ProcessK2GetSubmissionFromDatabase is working");
        $listsingups = HubspotSubmissions::where('was_submitted_successfully', null)->orwhere('was_submitted_successfully', 0)->limit(100)->get()->toArray();




        //pull currently signed up users to compare providers and form signups
        $readurl = "https://api.hubapi.com/contacts/v1/contact/emails/batch/?hapikey=" . $hubspotapikey;


        ///This function removes backslashes and quotes that interfere with the json strin
        function fixjson($element)
        {
            $fixedelement =  str_replace('\\', '', $element);
            $fixedelement = str_replace('"', '', $fixedelement);
            $fixedelement = str_replace("\n", '', $fixedelement);
            $fixedelement = str_replace("\r", '', $fixedelement);
            $fixedelement = preg_replace('/\t+/', '', $fixedelement);
            return $fixedelement;
        }

        foreach ($listsingups as $signup => $properties) {
            foreach ($properties as $key => $property) {
                //remove backslashees and double quotes that break json strings.
                $listsingups[$signup][$key] = fixjson($property);
            }

            $readurl = $readurl . "&email=" . str_replace(" ", "", $properties['email']);
        }


        $response = Http::get($readurl);

        $arrayresponse = json_decode($response->body());

        if (isset($arrayresponse->status)) {
            $updatearray =  array();
            foreach ($listsingups as $key => $databasesignee) {
                array_push($updatearray, $databasesignee['id']);
            }
            $query = DB::table('hubspot_submissions')->whereIn('id', $updatearray)->update(['was_submitted_successfully' => false]);
            die($response->body());
        }


        $signupjson = '[';
        foreach ($listsingups as $signup => $properties) {
            //var_dump($properties);

            //test if new subscriber varible initialization
            $isnewsubcriber = true;

            //initialize provider
            $finalprovider = $properties['tv_provider'];
            ///***** Begin special case for comma delimited multiple providers begin ****
            if (!empty((array)$arrayresponse)) {
                foreach ($arrayresponse as $subscriber) {

                    //If statments decides if provider exists at hubspot or is empty, if it doesn't exist
                    //or already exist move on otherwise if it is a new provider
                    //not in the hubspot list add it to a comma delimited list in the tv_provider field at hubspot.

                    if (strtolower($subscriber->properties->email->value) == strtolower(str_replace(" ", "", $properties['email']))) {
                        $isnewsubcriber = false;
                        if (!empty($subscriber->properties->tv_provider->value)) {
                            $hubspotprovidertemp = strtolower($subscriber->properties->tv_provider->value);
                            $databaseprovider = strtolower($properties['tv_provider']);
                            if (strpos($hubspotprovidertemp, $databaseprovider) !== false) {
                                $finalprovider = $subscriber->properties->tv_provider->value;
                            } else {
                                $finalprovider = $subscriber->properties->tv_provider->value . "," . $properties['tv_provider'];
                            }
                        } else {
                            $finalprovider = $properties['tv_provider'];
                        }
                    }
                }
            } else {
                $finalprovider = $properties['tv_provider'];
            }
            //sets the comma delimted providers if more than one in hubspot from above loop
            $properties['tv_provider'] = $finalprovider;
            ///******End special case for comma delimited multiple providers begin*******************


            $readurl = $readurl . "&email=" . str_replace(" ", "", $properties['email']);
            $signupjson = $signupjson .    '
                 {"email": "' . str_replace(" ", "", stripslashes($properties['email'])) . '",
                 "properties": [';
            if ($isnewsubcriber == true) {
                $signupjson = $signupjson . '
                     {
                         "property": "form_original_source",
                         "value": "' .  $properties['form_last_activity']  . '"
                     },';
            }


            foreach ($properties as $property => $value) {
                if ($property != 'email' && $property != 'id' &&  $property != 'timestamp' &&  $property != 'was_submitted_successfully' &&  $property != 'is_test') {

                    $signupjson = $signupjson . '
                   {
                       "property": "' .  $property  . '",
                       "value": "' .  stripslashes($value)  . '"
                   }';
                    if ($property !== 'how_did_you_hear_about_us') {
                        $signupjson = $signupjson . ',';
                    }
                }
            }
            $signupjson = $signupjson . '
                   ]
                 }';
            if ($signup !== array_key_last($listsingups)) {
                $signupjson = $signupjson . ',
       ';
            }
        }
        $signupjson = $signupjson . ']';



        // $curl = curl_init();
        $batchinserturl = 'https://api.hubapi.com/contacts/v1/contact/batch/?hapikey=' . $hubspotapikey;


        $response = Http::withBody(
            $signupjson,
            'application/json'
        )->post($batchinserturl);

        if ($response->successful()) {
            //mark subscribed

            if (!empty($listsingups)) {
                $updatearray =  array();
                foreach ($listsingups as $key => $databasesignee) {
                    array_push($updatearray, $databasesignee['id']);
                }

                $query = DB::table('hubspot_submissions')->whereIn('id', $updatearray)->update(['was_submitted_successfully' => true]);
            }
        } else {
            $updatearray =  array();
            foreach ($listsingups as $key => $databasesignee) {
                array_push($updatearray, $databasesignee['id']);
            }
            $query = DB::table('hubspot_submissions')->whereIn('id', $updatearray)->update(['was_submitted_successfully' => false]);
        }
    }
}
