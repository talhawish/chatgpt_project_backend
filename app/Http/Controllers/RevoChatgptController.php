<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;

class RevoChatgptController extends Controller
{

    public  $prompt = "";

    private $open_ai;


    public function __construct()
    {
        $open_ai_key = env('OPENAI_API_KEY');

        $this->open_ai = new OpenAi($open_ai_key);
    }

    public function generatePost($data)
    {


        $first_ask = "";

        $second_ask = "";

        $numberOfArticles = empty($data['number_of_articles']) ? 10 : $data['number_of_articles'];

        $numberOfArticles = empty($data['subtitles']) ? 10 : $data['subtitles'];

        $countNumberOfArticles = intval($numberOfArticles);


        $digit = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);

        $numberOfArticles = $digit->format($numberOfArticles);

        if (empty($data['keywords'])) {
            throw new \Exception('Please enter Keywords');
        } else {
            // $first_ask .= 'Baue mir eine Uberschrift zu "' . $data['keywords'] . '"';
            $first_ask .= 'Write me in german a title of no more than 70 characters with respecting upper and lower cases, using these keywords : "' . $data['keywords'] . '" ';
        }


        if (empty($data['kind'])) {
            throw new \Exception('Please enter company kind');
        } else {
            $first_ask .= ' kind of : "' . $data['kind'];
        }

        // $first_ask .= "Trennen Sie den Titel und die Beschreibung durch ,";

        if (empty($data['matchwords'])) {
            throw new \Exception('Please enter matchwords');
        } else {
            $second_ask .= 'Generate exactly ' . $numberOfArticles . ' Subtitles for the match words "' . $data['matchwords'] . '" and  : "' . $data['keywords'] . '"';
        }

        // Check the Grammar

        $first_ask .= " Give me the result with fixed german grammar and umlauts and respect upper cases, remove html tags ";
        // $first_ask .= " überprüfen Sie die Grammatik und umlauts ";

        // $second_ask .= 'give me the list separated by ,';
        // $second_ask .= ' und überprüfe die Grammatik, gib mir die Liste  getrennt durch , ';
        $second_ask .= ' Give me the result in german with fixed german grammar , Give me the result as a list seperated by "/" ';


        $this->ask($first_ask);


        $title =  $this->completetion();




        $title = $this->clean_title($title->text);


        $this->ask($second_ask);

        $articles =  $this->completetion();

        $articles = property_exists($articles, 'text') ? $articles->text : $articles;

        $articles = $this->articles_to_array($articles);

        $titleDescription = $this->generatePostDescription($title);

        $titleDescription = $this->clean($titleDescription);


        if (count($articles)    != $countNumberOfArticles) {

            throw new \Exception('Generated Post was wrong structured');
        }

        if (empty($title)) {

            throw new \Exception('Title to be regenerated');
        }

        if (empty($titleDescription)) {

            throw new \Exception('Title Description to be regenerated');
        }

        if (empty($articles)) {

            throw new \Exception('Articles to be regenerated');
        }

        return [
            "title" => $title,
            "titleDescription" => $titleDescription,
            "articles" => $articles
        ];
    }

    public function generate_post_title(): string
    {

        return '';
    }



    private function articles_to_array(string $articles): array
    {

        $articles = str_replace('\n', '', $articles);
        $articles = str_replace('"', '', $articles);

        $articles = explode('/',  $articles);


        foreach ($articles as $key => $article) {

            $articles[$key] = $this->clean_title($article);

            if (empty($articles[$key])) {
                unset($articles[$key]);
            }
        }

        return $articles;
    }


    public function generatePostContent($title)
    {


        $this->prompt = "";

        $this->ask('Write me in german a 200 word text for: "' . $title . '" ');

        // $this->ask('');
        // $this->ask('');

        $result = $this->completetion();


        $result = property_exists($result, 'text') ?  $result->text :  $result;

        if (empty($result)) {
            return  $this->generatePostContent($title);
        }

        return $result;
    }


    public function generatePostDescription($title)
    {


        $this->prompt = "";

        $this->ask('Write me in german a 30 word description text for: " ' . $title . '"');



        $result = $this->completetion();



        $result = property_exists($result, 'text') ?  $result->text :  "";



        return $result;
    }


    public function generateBulgarianPostDescription($title)
    {


        $this->prompt = "";

        $this->ask('Write me in bulgarian language a 30 word description text for: " ' . $title . '"');



        $result = $this->completetion();



        $result = property_exists($result, 'text') ?  $result->text :  "";



        return $result;
    }


    public function ask($text): void
    {
        $this->prompt .= '\n Humain : ' . $text;
        $this->prompt .= '\n Ai : ';
    }

    public function completetion($prompt = null)
    {

        $prompt = !is_null($prompt) ? $prompt : $this->prompt;



        try {
            $result = $this->open_ai->completion([

                "model" => "text-davinci-003",
                "prompt" => $prompt,
                'temperature' => 0.8,
                'max_tokens' => 1000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'echo'  => false,
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }


        $choices = property_exists(json_decode($result), 'choices') ? json_decode($result)->choices[0] : "";
        $text = property_exists(json_decode($result), 'text') ? json_decode($result)->text : "";


        // dd($text);
        // throw new \Exception($choices);
        // throw new \Exception($choices);

        // if(!empty($choices) ) {
        //     return $choices;
        // }







        if (is_null($choices)) {
            throw new \Exception("ChatGPT Return NULL Response, you have to resend the request again.");
        }

        // if(empty($choices) && empty($text)) {
        //     throw new \Exception("ChatGPT Return NULL Response, you have to resend the request again.");
        // }


        return $choices;
    }



    public function clean($string)
    {
        $string  = trim(preg_replace('/\s+/', ' ', $string));
        $string  = str_replace('\r\n', '', $string);
        $string = preg_replace(array('/[^a-zäöüß.,\'"!?0-9]абцдефгхийклмнопqрстувwхйз/i', '/[-]+/'), ' ', $string);

        return strip_tags(trim($string));
    }

    public function clean_title($string)
    {
        $string  = trim(preg_replace('/\s+/', ' ', $string));
        $string = preg_replace(array('/[^a-zäöüß.,\!?0-9]абцдефгхийклмнопqрстувwхйз/i', '/[-]+/'), ' ', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(' ', '/', $string);
        $string = str_replace('/', ' ', $string);
        $string = stripslashes($string);
        return strip_tags(trim($string));
    }



    public function generateBulgarianPost($data)
    {

        $first_ask = "";

        $second_ask = "";

        $numberOfArticles = empty($data['number_of_articles']) ? 10 : $data['number_of_articles'];

        $numberOfArticles = empty($data['subtitles']) ? 10 : $data['subtitles'];

        $countNumberOfArticles = intval($numberOfArticles);


        $digit = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);

        $numberOfArticles = $digit->format($numberOfArticles);

        if (empty($data['keywords'])) {
            throw new \Exception('Please enter Keywords');
        } else {
            // $first_ask .= 'Baue mir eine Uberschrift zu "' . $data['keywords'] . '"';
            $first_ask .= 'Write me in bulgarian a title consisting of a maximum of 70 characters depending on these keywords : "' . $data['keywords'] . '" ';
        }


        if (empty($data['kind'])) {
            throw new \Exception('Please enter company kind');
        } else {
            $first_ask .= ' the title should be the kind of : "' . $data['kind'];
        }

        // $first_ask .= "Trennen Sie den Titel und die Beschreibung durch ,";

        if (empty($data['matchwords'])) {
            throw new \Exception('Please enter matchwords');
        } else {
            $second_ask .= 'generate exactly ' . $numberOfArticles . ' Subtitles in bulgarian for matching words : "' . $data['matchwords'] . '" and  : "' . $data['keywords'] . '"';
        }

        // Check the Grammar

        $first_ask .= " Give the result in correct Bulgarian language and fixed grammar and non numeric list, ";
        // $first_ask .= " überprüfen Sie die Grammatik und umlauts ";

        // $second_ask .= 'give me the list separated by ,';
        // $second_ask .= ' und überprüfe die Grammatik, gib mir die Liste  getrennt durch , ';
        $second_ask .= 'separate between subtitles by "/"';


        $this->ask($first_ask);


        $title =  $this->completetion();



        $title = $this->clean_title($title->text);


        $this->ask($second_ask);

        // dd($title);

        $articles =  $this->completetion();

        // dd($articles);

        $articles = property_exists($articles, 'text') ? $articles->text : $articles;

        $articles = $this->articles_to_array($articles);



        $titleDescription = $this->generateBulgarianPostDescription($title);

        $titleDescription = $this->clean($titleDescription);


        // dd($articles, $title);
        if (count($articles)    != $countNumberOfArticles) {
            //   dd($articles);
            throw new \Exception('Generated Post was wrong structured');

            // return $this->generatePost($data);
        }
        // dd($title, $titleDescription, $articles);
        return [
            "title" => $title,
            "titleDescription" => $titleDescription,
            "articles" => $articles
        ];
    }


    public function sheets_test(Request $request)
    {

        $this->ask('Generate 5 titles using these keywords : ' . $request->keywords . 'and separate them using "/"');


        $titles = $this->completetion();

        $titles = property_exists($titles, 'text') ? $titles->text : $titles;

        $titles = $this->articles_to_array($titles);

        return $titles;
    }

    public function google_photo($keyword)
    {


        $image_str = "";
        # Only do this if we've already passed in a keyword (i.e. it's not blank)
        if ($keyword != "") {
            # Load the data from Google via cURL
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, "https://ajax.googleapis.com/ajax/services/search/images?v=1.0&imgsz=xlarge&q=" . urlencode($keyword));
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            $contents = curl_exec($curl_handle);
            curl_close($curl_handle);


            $images = $this->string_extractor($contents, 'unescapedUrl":"', '",');


            foreach ($images as $image) {
                $image_str .= "<img src='" . $image . "' class='graphic-choice graphic-search-image'>";
            }
        }

        return $image_str;
    }

    /*-------------------------------------------------------------------------------------------------
        Returns array of strings found between two target strings
-------------------------------------------------------------------------------------------------*/
    public function string_extractor($string, $start, $end)
    {

        # Setup
        $cursor = 0;
        $foundString             = -1;
        $stringExtractor_results = array();

        # Extract
        while ($foundString != 0) {
            $ini = strpos($string, $start, $cursor);

            if ($ini >= 0) {
                $ini    += strlen($start);
                $len     = strpos($string, $end, $ini) - $ini;
                $cursor  = $ini;
                $result  = substr($string, $ini, $len);
                array_push($stringExtractor_results, $result);
                $foundString = strpos($string, $start, $cursor);
            } else {
                $foundString = 0;
            }
        }

        return $stringExtractor_results;
    }
}
