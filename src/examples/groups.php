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

require_once "__init__.php";

if ($osapi) {
  if ($strictMode) {
    $osapi->setStrictMode($strictMode);
  }
  
  // Start a batch so that many requests may be made at once.
  $batch = $osapi->newBatch();

  $user_params = array(
  	'userId' => $userId
  );
  $batch->add($osapi->groups->get($user_params), 'getGroups');
  
  $batch->add($osapi->groups->getSupportedFields($user_params), 'getSupportedFields');

  // Send the batch request.
  $result = $batch->execute();
?>

<h1>Messages Example</h1>
<h2>Request:</h2>
<p>This sample attempted to fetch a user's groups. The fetching supportedFields for groups.</p>

<?php
    require_once('response_block.php');
}
