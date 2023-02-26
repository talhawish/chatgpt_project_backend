<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ChatgptController;
use App\Http\Controllers\MerchChatgptController;
use Illuminate\Http\Request;

class ChatgptApiController extends Controller
{
    public $response;
    public $merch;

    public function __construct()
    {
        $this->merch = new  MerchChatgptController();
        $this->response = [];
    }
    public function fetch()
    {
        $this->merch->ask('

        Generate 5 titles for my tshirt must contain 50 to 60 charachters at max consist of the following keywords : 
        "i can\'t i have plans " the titles should be in the kind of : "gaming", generate also four bullet points for each title and give me the result in anice foratted list

        ');

        dd($this->merch->completetion()->text);
        // $this->response = $response;

        return response()->json($this->response);
    }

    public function send(Request $request)
    {

        // $response = $this->merch->generateTitles("I can\'t I have plans in my room", "gaming")
        $titles = $this->merch->generateTitles($request->number_of_titles, $request->keywords, $request->niche);
        $this->response = ['titles' => $titles];

        return response()->json($this->response);
    }

    public function test()
    {
        $this->merch->ask('

        Generate 5 titles for my tshirt must contain 50 to 60 charachters at max consist of the following keywords : 
        "i can\'t i have plans " the titles should be in the kind of : "gaming", generate also four bullet points for each title and give me the result in anice foratted list

        ');
        $this->response = $this->merch->completetion()->text;
        // dd($this->merch->completetion()->text);
        // $this->response = $response;

        return response()->json($this->response);
    }
}