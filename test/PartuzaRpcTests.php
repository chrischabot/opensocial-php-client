<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * 'License'); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * 'AS IS' BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

/*
 * This file is meant to be run through a php command line, not called
 * directly through the web browser. To run these tests from the command line:
 * # cd /path/to/client
 * # phpunit test/PartuzaRpcTests.php
 */

require_once '__init__.php';
require_once 'online/OnlineTestSuite.php';

class PartuzaRpcTests extends OnlineTestSuite {
  public $CONSUMER_KEY = 'e2c2d2dd-e6c4-c4df-b2c4-d6efd2dcffd1';
  public $CONSUMER_SECRET = 'eb214eedcda39f3440c43b623806912f';
  public $USER_A_ID = '1311';
  public $USER_A_DISPLAY_NAME = 'Alice Testington';

  protected function getOsapi() {
    $provider = new osapiPartuzaProvider();
    $auth = new osapiOAuth2Legged($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $this->USER_A_ID);
    return new osapi($provider, $auth);
  }

  public static function suite() {
    return new PartuzaRpcTests();
  }
}
