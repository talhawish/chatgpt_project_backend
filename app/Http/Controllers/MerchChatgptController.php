<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;

class MerchChatgptController extends Controller
{
    private  $prompt = "";

    private $open_ai;


    public function __construct()
    {
        $open_ai_key = env('OPENAI_API_KEY');

        $this->open_ai = new OpenAi($open_ai_key);

        $this->bootstrap();
    }


    public function bootstrap() {
        $this->ask('Hello, I\m a graphic designer and I work on merch by amazon, I design t-shirts, take this in consideration to answer my next questions');
    }

    public function index()
    {

     
        
        

     
        dd($secondAnswer);

        $this->ask('generate two exactly bullet points of 150 charachters at minimum,  included my title and saty at the same niche, seperate the result by "_"');

        $thirdAnswer = $this->articles_to_array($this->completetion()->text);
        
        // $this->ask('wha');

        // $fourthdAnswer = $this->clean($this->completetion()->text);

        
       

        dd($firstAnswer, $secondAnswer, $thirdAnswer);


        $data = collect([]);

        $data->push([$firstAnswer]);

        return $data;
    }

    public function generateTitles($number = 1, $keywords, $niche) {
        $this->ask('
        based on search volume  amazon  give me '.$number.' titles for my POD design , must contain between 50 to 60 charachters at max consist of the following keywords : 
            "'.$keywords.'", the titles should be in the ncihe of : "'.$niche.'", 
            generate also four bullet points must contain between 190 to 200 charachters 
            and should\'t talk about tissue quality or materials that tshirt made with or it must be talking only about the design 
            and give me the result in a nice formatted list
    
        ');



        return $this->completetion()->text;
        // return $this->articles_to_array($this->completetion()->text);

    }


    public function completetion($prompt = null) {

        $prompt = !is_null($prompt) ? $prompt : $this->prompt;



        try {
            $result = $this->open_ai->completion([
                "model" => "text-davinci-003",
                "prompt"=> $prompt,
                'temperature' => 0.9,
                'max_tokens' => 3500,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'echo'  => false,
            ]);
            
        } catch(\Exception $e) {
        echo $e->getMessage();
        }
        
        
       
        $choices = property_exists(json_decode($result), 'choices') ? json_decode($result)->choices[0] : "";
        $text = property_exists(json_decode($result), 'text') ? json_decode($result)->text : "";

        if(empty($choices) and empty($text)) {
            throw new \Exception("ChatGPT Return NULL Response, you have to resend the request again.");
        }

        
        return $choices;
    }


    public function ask($text) : void
    {
        $this->prompt .= '\n Humain : ' . $text;
        $this->prompt .= '\n Ai : ';
    }

    private function articles_to_array(string $articles): array
    {
        
        $articles = str_replace('\n', '', $articles);
        $articles = str_replace('"', '', $articles);
    
        $articles = explode('__',  $articles);

        
        foreach ($articles as $key => $article) {

            $articles[$key] = $this->clean($article);

            if(empty($articles[$key])) {
                unset($articles[$key]);
            }

        }
      
        return $articles;
    }


    public function clean($string) {
        $string = preg_replace(array('/[^a-zäöüß.,\'!?0-9]/i', '/[-]+/') , ' ', $string);
         return trim($string);
     }


}
