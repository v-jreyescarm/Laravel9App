 <?php

  /*

  ==========================================================================
    DO NOT DELETE THIS PAGE!!!
  ==========================================================================

  The Azure Web App that is hosting this application has a feature, called 'Health Check', that pings this page every ~10 minutes to see if the page responds with a '200' status code. If this page becomes unresponsive, the Azure Health Check feature will automatically destroy, then re-spawn the container instance.

*/
  echo '<h1>Azure Health Check page</h1><br><h3>This page is called by the Health Check feature in Azure App Services.</h3>';
  ?>
