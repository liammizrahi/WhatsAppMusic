<?php

/*
* CREATED BY LIAM MIZRAHI
* Use as a Laravel controller
* Be aware this system use at least 2 third party providers
* to use and maintain this system properly you need to configure an API key at RapidAPI
*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WAServer extends Controller
{
  protected $pn;

  // youtube section
  public function youtube(Request $request)
  {
    $this->pn = $request->input('number');
    $search_term = $request->input('message');

    $this->send_yt_number($this->pn, "מחפש : ".$search_term);

    $curl = curl_init();

    curl_setopt_array($curl, [
    	CURLOPT_URL => "https://youtube-search-results.p.rapidapi.com/youtube-search/?q=".urlencode($search_term),
    	CURLOPT_RETURNTRANSFER => true,
    	CURLOPT_FOLLOWLOCATION => true,
    	CURLOPT_ENCODING => "",
    	CURLOPT_MAXREDIRS => 10,
    	CURLOPT_TIMEOUT => 30,
    	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    	CURLOPT_CUSTOMREQUEST => "GET",
    	CURLOPT_HTTPHEADER => [
    		"X-RapidAPI-Host: youtube-search-results.p.rapidapi.com",
    		"X-RapidAPI-Key: ".env('search_key', 'DEFAULT_SEARCH_KEY')
    	],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if(is_null(json_decode($response)->items[0]->url)) {
      $this->send_yt_number($this->pn, "לא נמצאו תוצאות");
      return;
    }

    $this->yt_download(json_decode($response)->items[0]->url);

  }
  protected function yt_download($url) {

      $curl = curl_init();

      curl_setopt_array($curl, [
      	CURLOPT_URL => "https://t-one-youtube-converter.p.rapidapi.com/api/v1/createProcess?url=".urlencode($url)."&format=mp3&responseFormat=json&lang=he",
      	CURLOPT_RETURNTRANSFER => true,
      	CURLOPT_FOLLOWLOCATION => true,
      	CURLOPT_ENCODING => "",
      	CURLOPT_MAXREDIRS => 10,
      	CURLOPT_TIMEOUT => 30,
      	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      	CURLOPT_CUSTOMREQUEST => "GET",
      	CURLOPT_HTTPHEADER => [
      		"X-RapidAPI-Host: t-one-youtube-converter.p.rapidapi.com",
      		"X-RapidAPI-Key: ".env('execute_key', 'DEFAULT_SEARCH_KEY')
      	],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      try {
        if(property_exists(json_decode($response), "guid")) {
          sleep(2);
          $this->yt_execute(json_decode($response)->guid);
        }
        else {
          $this->send_yt_number($this->pn, "התהליך נכשל! בעיית מערכת, יש לנסות שוב מאוחר יותר");
        }
      }
      catch (Exception $e) {
        $this->send_yt_number($this->pn, "התהליך נכשל!");
      }
  }
  protected function yt_execute($guid) {

    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => "https://t-one-youtube-converter.p.rapidapi.com/api/v1/statusProcess?guid=".$guid."&responseFormat=json&lang=he",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
      "X-RapidAPI-Host: t-one-youtube-converter.p.rapidapi.com",
      "X-RapidAPI-Key: ".env('execute_key', 'DEFAULT_SEARCH_KEY')
    ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
      try {
        if(property_exists(json_decode($response), "file")) {
          $this->send_yt_number($this->pn, "מצאתי!");
          $this->send_media_number($this->pn, json_decode($response)->file);
          echo json_decode($response)->file;
        }
        else {
          $this->send_yt_number($this->pn, "קרה לי משהו בדרך, תנסו לחפש שוב רגע אותו דבר");
        }
      }
      catch (Exception $e) {
        $this->send_yt_number($this->pn, "קרה לי משהו בדרך, תנסו לחפש שוב רגע אותו דבר");
      }
    }
  }

  protected function send_yt_number($number, $message) {

    $url = "https://liam-business-whatsapp.herokuapp.com/send-message";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "number=".$number."&message=".urlencode($message);

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    var_dump($resp);

  }
  protected function send_media_number($number, $media) {

    $url = "https://liam-business-whatsapp.herokuapp.com/send-media";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = "number=".$number."&caption=test&file=".urlencode($media);

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    var_dump($resp);

  }
  // end youtube
}
