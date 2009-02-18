<?php 
/*
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */ 

require_once "_examples_common.php";

if ($osapi) {
  if ($strictMode) {
    $osapi->setStrictMode($strictMode);
  }
  
  // Start a batch so that many requests may be made at once.
  $batch = $osapi->newBatch();
  
  // Get the current user's app data
  $app_data_self_params = array(
      'userId' => $userId, 
      'groupId' => '@self', 
      'appId' => $appId,
      'fields' => array('*')
  );
  $batch->add($osapi->appdata->get($app_data_self_params), 'appdataSelf');

  // Get the app data for the user's friends
  $app_data_friends_params = array(
      'userId' => $userId, 
      'groupId' => '@friends', 
      'appId' => $appId,
      'fields' => array('*')
  );
  $batch->add($osapi->appdata->get($app_data_friends_params), 'appdataFriends');
  
  // Create some app data for the current user 
  $create_params = array(
      'userId' => $userId, 
      'groupId' => '@self', 
      'appId' => $appId,
      'data' => array(
          'osapiFoo1' => 'bar1', 
          'osapiFoo2' => 'bar2', 
          'osapiFoo3' => 'bar3'
      )
  );
  $batch->add($osapi->appdata->create($create_params), 'createAppData');
  
  // Update app data for the current user
  $update_params = array(
      'userId' => $userId, 
      'groupId' => '@self', 
      'appId' => $appId,
      'data' => array(
          'osapiFoo1' => 'newBar1'
      )
  );
  $batch->add($osapi->appdata->update($update_params), 'updateAppData');
  
  // Get the app data again to show the updated value
  $get_params = array(
      'userId' => $userId, 
      'groupId' => '@self', 
      'appId' => $appId,
      'fields' => array(
          'osapiFoo1', 
          'osapiFoo2', 
          'osapiFoo3'
      )
  );
  $batch->add($osapi->appdata->get($get_params), 'getAppData');
  
  // Delete the keys we created in the previous examples
  $delete_params = array(
      'userId' => $userId, 
      'groupId' => '@self',
      'appId' => $appId, 
      'fields' => array(
          'osapiFoo1', 
          'osapiFoo2', 
          'osapiFoo3'
      )
  );
  $batch->add($osapi->appdata->delete($delete_params), 'deleteAppData');

  
  /*
   * Updating, fetching, and deleting will actually work since the batch is 
   * executed in order.   The get should return:
   * [getAppData] => Array
        (
            [USER_ID] => Array
                (
                    [osapiFoo2] => bar2
                    [osapiFoo3] => bar3
                    [osapiFoo1] => newBar1
                )
        )
  * and create/update/delete should be empty result sets, aka 
  * [BATCH_ID] => Array (). If an error occured the result will be a 
  * osapiError, so you should check the result using 
  * "if ($result[$idx] instanceof osapiError) .. "
  */
  
  // Send the batch request.
  $result = $batch->execute();
?>

<h1>App Data Example</h1>

<h2>Request:</h2>
<p>This sample fetched all of the app data for the current user and their 
  friends.  Then it set app data for the keys <em>osapiFoo1</em>, 
  <em>osapiFoo2</em>, and <em>osapiFoo3</em>, updated the key 
  <em>osapiFoo1</em> with a new value, fetched all three fields again, and
  then deleted the three keys in the same batch request.</p>

<?php

  // Demonstrate iterating over a response set, checking for an error,
  // and working with the result data.
  
  foreach ($result as $key => $result_item) {
    if ($result_item instanceof osapiError) {
      $code = $result_item->getErrorCode();
      $message = $result_item->getErrorMessage();
      echo "<h2>There was a <em>$code</em> error with the <em>$key</em> request:</h2>";
      echo "<pre>";
      echo htmlentities($message);
      echo "</pre>";
    } else {
      echo "<h2>Response for the <em>$key</em> request:</h2>";
      echo "<pre>";
      echo htmlentities(print_r($result_item, True));
      echo "</pre>";
    }
  }
}
