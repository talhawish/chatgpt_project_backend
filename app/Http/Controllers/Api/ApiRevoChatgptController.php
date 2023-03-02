<?php

namespace App\Http\Controllers\Api;

use DOMDocument;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ChatgptController;
use Buchin\GoogleImageGrabber\GoogleImageGrabber;
use App\Http\Controllers\RevoChatgptController as Revo;
use Illuminate\Support\Str;


class ApiRevoChatgptController extends Controller
{
    public $response;
    public $revo;

    public $level;

    public function __construct()
    {
        $this->revo = new  Revo();
        $this->response = [];
    }


    public function bootstrap(Request $request)
    {

        $this->level  = $request->level ?? "High School";

        $this->revo->ask('

        I will give you prompts and you should follow my rules :
            - your response should be directly don\'t tell me about yourself and what you can do.
            - I want you to give me the result in a nice HTML format.
            - dont use "\n" to break lines use "</br>" instead.
            - use this tag <h1 id="title"></h1> for the principle generated title.
            - use this tag <p id="content"></p> for the rest of all generated content .
            - consider that your are generating content for ' . $this->level . ' level.

         ');
    }
    public function generate_first_page(Request $request)
    {

        $keywords = $request->keywords ?? " ";

        $this->bootstrap($request);

        if ($request->type == 1) {

            $this->revo->ask('Based on these keywords : ' . $keywords . 'generate   Exam Paper and Marking Scheme ');
        }

        if ($request->type == 2) {

            $this->revo->ask('generate between 5 to 10 tutorials of 300 words about : ' . $keywords);
        }

        if ($request->type == 3) {
            $this->revo->ask('generate presentation slide of maximum 300 words about : ' . $keywords . " it is first slide of presentation");
        }

        $this->response['content'] = property_exists($this->revo->completetion(), 'text') ? $this->revo->completetion()->text : $this->revo->completetion();


        // $this->response['image'] = $this->revo->google_photo("apples");

        return response()->json($this->response);
        // return $titles;
    }

    public function generate_exam(Request $request)
    {

        $keywords = $request->keywords ?? " ";

        $this->bootstrap($request);

        $this->revo->ask('generate an exam of ' . $request->length . ' questions about : ' . $keywords);

        $this->response['content'] = property_exists($this->revo->completetion(), 'text') ? $this->revo->completetion()->text : $this->revo->completetion();

        return response()->json($this->response);
        // return $titles;
    }


    public function generate_tutorial(Request $request)
    {

        $keywords = $request->keywords ?? " ";

        $this->bootstrap($request);

        $this->revo->ask('generate between 5 to 10 tutorials of 300 words about : ' . $keywords);


        $this->response['content'] = property_exists($this->revo->completetion(), 'text') ? $this->revo->completetion()->text : $this->revo->completetion();

        return response()->json($this->response);
        // return $titles;
    }

    public function generate_presentation(Request $request)
    {

        $keywords = $request->keywords ?? " ";

        $this->bootstrap($request);

        $page = $request->page ?? 1;
        $this->revo->ask('generate a presentation of 300 words about : ' . $keywords . ' its page ' . $page);

        $this->response['content'] = property_exists($this->revo->completetion(), 'text') ? $this->revo->completetion()->text : $this->revo->completetion();

        return response()->json($this->response);
        // return $titles;
    }

    public function test()
    {



        dd($this->revo->completetion("how to say hi in spanish?"));


        $this->response['content'] = property_exists($this->revo->completetion(), 'text') ? $this->revo->completetion()->text : $this->revo->completetion();
    }
}
