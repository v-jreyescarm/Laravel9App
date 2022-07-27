<?php

namespace App\Actions;

use App\Models\K2CacheSubmissions;
use Illuminate\Support\Facades\Http;


class ProcessK2GetSubmissionFromDatabase
{


    public static function handle()
    {
        // DEBUG:
        // \Log::info("ProcessK2GetSubmissionFromDatabase is working");

        $K2CacheSubmissions = K2CacheSubmissions::where('was_submitted_successfully', null)->orwhere('was_submitted_successfully', 0)->limit(100)->get();

        foreach ($K2CacheSubmissions as $K2CacheSubmission) {

            //get k2 userid from k2 api if blank in the queue
            if (empty($K2CacheSubmission->k2userid)) {


                $xml = '<?xml version="1.0" encoding="UTF-8"?><K2DATACOLLECTOR>
                <CLIENT>D912A11D</CLIENT>
                <ZIPCODE>' . $K2CacheSubmission->zipcode . '</ZIPCODE>
                </K2DATACOLLECTOR>';


                $response = Http::withBody(
                    $xml,
                    'application/xml'
                )->post('http://xmlapi.viewerlink.tv/collectXML.asp');


                // $body = json_decode(json_encode(simplexml_load_string($response->body())));


                if ($response->successful()) {

                    $xmlbody =   $response->body();

                    $xmlObject = simplexml_load_string($xmlbody);
                    $jsonFormatData = json_encode($xmlObject);
                    $result = json_decode($jsonFormatData, true);

                    if ($result['K2STATUS'] == 'SUCCESS') {
                        $K2CacheSubmission->k2userid = $result['K2USERID'];
                    } else {
                        K2CacheSubmissions::where('id', $K2CacheSubmission->id)
                            ->update(['was_submitted_successfully' => 0]);
                        die('k2connection failed');
                    }
                } else {
                    K2CacheSubmissions::where('id', $K2CacheSubmission->id)
                        ->update(['was_submitted_successfully' => 0]);
                    die('k2connection failed');
                }
            }

            //Derive type from crisis code
            if ($K2CacheSubmission->crisis  == 0) {
                $type = '';
            } else if ($K2CacheSubmission->crisis == 2) {
                $type = 'Thank You';
            } else if ($K2CacheSubmission->crisis  == 1) {
                $type = 'Demand';
            } else if ($K2CacheSubmission->crisis  == 3) {
                $type = 'HD Demand';
            } else if ($K2CacheSubmission->crisis  == 4) {
                $type = 'Custom Demand';
            }


            $data = '<?xml version="1.0" encoding="UTF-8"?><K2DATACOLLECTOR>
            <CLIENT>D912A11D</CLIENT>
            <K2USERID>' . $K2CacheSubmission->k2userid . '</K2USERID>
            <FIRSTNAME>' . $K2CacheSubmission->firstname . '</FIRSTNAME>
            <LASTNAME>' . $K2CacheSubmission->lastname . '</LASTNAME>
            <EMAILADDRESS>' . $K2CacheSubmission->emailaddress . '</EMAILADDRESS>
            <ZIPCODE>' . $K2CacheSubmission->zipcode . '</ZIPCODE>
            <PROVIDER>' . $K2CacheSubmission->provider . '</PROVIDER>
            <OPTIN>' . $K2CacheSubmission->optin . '</OPTIN>
            <SOURCE>' . $K2CacheSubmission->source . '</SOURCE>
            <EMAILTYPE>' . $type . '</EMAILTYPE>
            ';
            if (!empty($K2CacheSubmission->emailmessage)) {
                $data = $data .
                    '<EMAILMESSAGE>' . $K2CacheSubmission->emailmessage . '</EMAILMESSAGE>
            ';
            }
            $data = $data .

                '<CRISIS>' . $K2CacheSubmission->crisis . '</CRISIS>
            </K2DATACOLLECTOR>';
            $url = 'http://xmlapi.viewerlink.tv/UserXML.asp';


            $response = Http::withBody(
                $data,
                'application/xml'
            )->post($url);



            if ($response->successful()) {

                $xmlbody =   $response->body();

                $xmlObject = simplexml_load_string($xmlbody);
                $jsonFormatData = json_encode($xmlObject);
                $result = json_decode($jsonFormatData, true);
                if ($result['K2STATUS'] == 'SUCCESS') {
                    K2CacheSubmissions::where('id', $K2CacheSubmission->id)
                        ->update(['was_submitted_successfully' => 1]);
                } else {
                    K2CacheSubmissions::where('id', $K2CacheSubmission->id)
                        ->update(['was_submitted_successfully' => 0]);
                }
            } else {
                K2CacheSubmissions::where('id', $K2CacheSubmission->id)
                    ->update(['was_submitted_successfully' => 0]);
            }
        }
    }
}
