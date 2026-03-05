<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class TextTranslator extends Controller
{
    public function index(Request $request)
    {
       return view('admin.textTranslator.translator');
    }


    public function transliterateToHindi($text) {
        // LOCAL
         $url = "http://www.google.com/inputtools/request?text=" . urlencode($text) . "&itc=hi-t-i0-und&num=1";
        
        // $url = "https://www.google.com/inputtools/request?text=" . urlencode($text) . "&itc=hi-t-i0-und&num=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
    
        $response = json_decode($response, true);
        $text = '';
        if (isset($response[1])) {
            foreach ($response[1] as $key) {
                if (isset($key[1][0])) {
                    $text .= '' . $key[1][0] . '';
                }
            }
            return $text;
        } else {
            return $text;
        }
    }


    public function unicodeToKrutiDev1($unicodeSubstring) {
        // Arrays for mapping Unicode to KrutiDev
        $arrayOne = [
            "‘", "’", "“", "”", "(", ")", "{", "}", "=", "।", "?", "-", "µ", "॰", ",", ".", "् ",
            "०", "१", "२", "३", "४", "५", "६", "७", "८", "९", "x",
            "फ़", "फ़्", "क़", "ख़", "ग़", "ज़्", "ज़", "ड़", "ढ़", "फ़", "य़", "ऱ", "ऩ",
            "त्त्", "त्त", "क्त", "दृ", "कृ",
            "ह्न", "ह्य", "हृ", "ह्म", "ह्र", "ह्", "द्द", "क्ष्", "क्ष", "त्र्", "त्र", "ज्ञ",
            "छ्य", "ट्य", "ठ्य", "ड्य", "ढ्य", "द्य", "द्व",
            "श्र", "ट्र", "ड्र", "ढ्र", "छ्र", "क्र", "फ्र", "द्र", "प्र", "ग्र", "रु", "रू",
            "्र",
            "ओ", "औ", "आ", "अ", "ई", "इ", "उ", "ऊ", "ऐ", "ए", "ऋ",
            "क्", "क", "क्क", "ख्", "ख", "ग्", "ग", "घ्", "घ", "ङ",
            "चै", "च्", "च", "छ", "ज्", "ज", "झ्", "झ", "ञ",
            "ट्ट", "ट्ठ", "ट", "ठ", "ड्ड", "ड्ढ", "ड", "ढ", "ण्", "ण",
            "त्", "त", "थ्", "थ", "द्ध", "द", "ध्", "ध", "न्", "न",
            "प्", "प", "फ्", "फ", "ब्", "ब", "भ्", "भ", "म्", "म",
            "य्", "य", "र", "ल्", "ल", "ळ", "व्", "व",
            "श्", "श", "ष्", "ष", "स्", "स", "ह",
            "ऑ", "ॉ", "ो", "ौ", "ा", "ी", "ु", "ू", "ृ", "े", "ै",
            "ं", "ँ", "ः", "ॅ", "ऽ", "् ", "्"
        ];
    
        $arrayTwo = [
            "^", "*", "Þ", "ß", "¼", "½", "¿", "À", "¾", "A", "\\", "&", "&", "Œ", "]", "-", "~ ",
            "å", "ƒ", "„", "…", "†", "‡", "ˆ", "‰", "Š", "‹", "Û",
            "Q", "¶", "d", "[k", "x", "T", "t", "M+", "<+", "Q", ";", "j", "u",
            "Ù", "Ùk", "Dr", "–", "—",
            "à", "á", "â", "ã", "ºz", "º", "í", "{", "{k", "«", "=", "K",
            "Nî", "Vî", "Bî", "Mî", "<î", "|", "}",
            "J", "Vª", "Mª", "<ªª", "Nª", "Ø", "Ý", "æ", "ç", "xz", "#", ":",
            "z",
            "vks", "vkS", "vk", "v", "bZ", "b", "m", "Å", ",s", ",", "_",
            "D", "d", "ô", "[", "[k", "X", "x", "?", "?k", "³",
            "pkS", "P", "p", "N", "T", "t", "÷", ">", "¥",
            "ê", "ë", "V", "B", "ì", "ï", "M", "<", ".", ".k",
            "R", "r", "F", "Fk", ")", "n", "/", "/k", "U", "u",
            "I", "i", "¶", "Q", "C", "c", "H", "Hk", "E", "e",
            "¸", ";", "j", "Y", "y", "G", "O", "o",
            "'", "'k", "\"", "\"k", "L", "l", "g",
            "v‚", "‚", "ks", "kS", "k", "h", "q", "w", "`", "s", "S",
            "a", "¡", "%", "W", "·", "~ ", "~"
        ];
    
        $arrayOneLength = count($arrayOne);
    
        // Specialty characters replacements
        $specialReplacements = [
            "क़" => "क़",
            "ख़‌" => "ख़",
            "ग़" => "ग़",
            "ज़" => "ज़",
            "ड़" => "ड़",
            "ढ़" => "ढ़",
            "ऩ" => "ऩ",
            "फ़" => "फ़",
            "य़" => "य़",
            "ऱ" => "ऱ",
            "ि" => "f"
        ];
    
        // Replace specialty characters
        foreach ($specialReplacements as $search => $replace) {
           
            $unicodeSubstring = str_replace($search, $replace, $unicodeSubstring);
    
        } 
        // Replace Unicode with ASCII
        for ($i = 0; $i < $arrayOneLength; $i++) {
            $unicodeSubstring = str_replace($arrayOne[$i], $arrayTwo[$i], $unicodeSubstring);
        }
        
        // Move "f" to the correct position
        $unicodeSubstring = "  " . $unicodeSubstring . "  ";
        $positionOfF = strpos($unicodeSubstring, "f"); 
        // print_r("\npositionOfF1:- ". $positionOfF);
        while ($positionOfF !== false) { 
            $combination = $unicodeSubstring[$positionOfF-2].$unicodeSubstring[$positionOfF-1];
         
                if (in_array($combination,$arrayTwo)) 
                {
                    $unicodeSubstring = substr_replace($unicodeSubstring,  $unicodeSubstring[$positionOfF].$unicodeSubstring[$positionOfF - 2].$unicodeSubstring[$positionOfF - 1], $positionOfF - 2, 3);
                   
             }else{
                $unicodeSubstring = substr_replace($unicodeSubstring,  $unicodeSubstring[$positionOfF].$unicodeSubstring[$positionOfF - 1], $positionOfF - 1, 2);
 
            } 
            $positionOfF = strpos($unicodeSubstring, "f", $positionOfF + 1);
            
        } 

        
      
     
        $unicodeSubstring = trim($unicodeSubstring);
       
        // Move "half R" to the correct position and replace
        $unicodeSubstring = "  " . $unicodeSubstring . "  ";
        $positionOfR = strpos($unicodeSubstring, "j~");
        $setOfMatras = ["‚", "ks", "kS", "k", "h", "q", "w", "`", "s", "S", "a", "¡", "%", "W", "·", "~ ", "~"];
        while ($positionOfR !== false) {
            $unicodeSubstring = str_replace("j~", "", $unicodeSubstring, $count);
            if ($count > 0 && in_array($unicodeSubstring[$positionOfR + 1], $setOfMatras)) {
                $unicodeSubstring = substr_replace($unicodeSubstring, "Z", $positionOfR + 2, 0);
            } else {
                $unicodeSubstring = substr_replace($unicodeSubstring, "Z", $positionOfR + 1, 0);
            }
            $positionOfR = strpos($unicodeSubstring, "j~");
        }
        $unicodeSubstring = trim($unicodeSubstring);
    
    
        
        return $unicodeSubstring;
    }

    public function textTranslate(Request $request){
        $text = $request->post('text'); 
        $convertTo =$request->post('convertTo');
        $value = '';
        if( $convertTo == 'hindi'){
            $value = $this->transliterateToHindi($text); 
        }else{
           
            $value = $this->unicodeToAkruti($text); 
        }
        echo json_encode([
            'value' => $value
        ]);
    }

    public function unicodeToKrutiDev($inputText) {
        // Character mappings
        $arrayOne = [
           "‘","’","“","”","(",")","{","}","=","।","?","-","µ","॰",",",".","् ", 
            "०","१","२","३","४","५","६","७","८","९","x","+",";","_",

            "फ़्","क़","ख़","ग़","ज़्","ज़","ड़","ढ़","फ़","य़","ऱ","ऩ",    // one-byte nukta varNas
            "त्त्","त्त","क्त","दृ","कृ",

            "श्व","ह्न","ह्य","हृ","ह्म","ह्र","ह्","द्द","क्ष्","क्ष","त्र्","त्र","ज्ञ",
            "छ्य","ट्य","ठ्य","ड्य","ढ्य","द्य","द्व",
            "श्र","ट्र","ड्र","ढ्र","छ्र","क्र","फ्र","द्र","प्र","ग्र","रु","रू",
            "्र",

            "ओ","औ","आ","अ","ई","इ","उ","ऊ","ऐ","ए","ऋ",

            "क्","क","क्क","ख्","ख","ग्","ग","घ्","घ","ङ",
            "चै","च्","च","छ","ज्","ज","झ्","झ","ञ",

            "ट्ट","ट्ठ","ट","ठ","ड्ड","ड्ढ","ड","ढ","ण्","ण",  
            "त्","त","थ्","थ","द्ध","द","ध्","ध","न्","न",  

            "प्","प","फ्","फ","ब्","ब","भ्","भ","म्","म",
            "य्","य","र","ल्","ल","ळ","व्","व", 
            "श्", "श",  "ष्", "ष",  "स्",   "स",   "ह",     

            "ऑ","ॉ","ो","ौ","ा","ी","ु","ू","ृ","े","ै",
            "ं","ँ","ः","ॅ","ऽ","् ","्","़","/"
        ];
    
        $arrayTwo = [
            "^", "*", "Þ", "ß", "¼", "½", "¿", "À", "¾", "A", "\\", "&", "&", "Œ", "]", "-", "~ ",
            "å", "ƒ", "„", "…", "†", "‡", "ˆ", "‰", "Š", "‹", "Û", "$", "(", "&",
            "¶+", "d+", "[k+", "x+", "T+", "t+", "M+", "<+", "Q+", ";+", "j+", "u+",
            "Ù", "Ùk", "ä", "–", "—",
            "Üo", "à", "á", "â", "ã", "ºz", "º", "í", "{", "{k", "«", "=", "K",
            "Nî", "Vî", "Bî", "Mî", "<î", "|", "}",
            "J", "Vª", "Mª", "<ªª", "Nª", "Ø", "Ý", "æ", "ç", "xz", "#", ":",
            "z",
            "vks", "vkS", "vk", "v", "bZ", "b", "m", "Å", ",s", ",", "_",
            "D", "d", "ô", "[", "[k", "X", "x", "?", "?k", "³",
            "pkS", "P", "p", "N", "T", "t", "÷", ">", "¥",
            "ê", "ë", "V", "B", "ì", "ï", "M", "<", ".", ".k",
            "R", "r", "F", "Fk", ")", "n", "è", "èk", "U", "u",
            "I", "i", "¶", "Q", "C", "c", "H", "Hk", "E", "e",
            "¸", ";", "j", "Y", "y", "G", "O", "o",
            "'", "'k", "\"", "\"k", "L", "l", "g",
            "v‚", "‚", "ks", "kS", "k", "h", "q", "w", "`", "s", "S",
            "a", "¡", "%", "W", "·", "~ ", "~", "+", "@",','
        ];
    
        // Process input text
        $textSize = strlen($inputText);
        $maxTextSize = 6000; // Maximum chunk size
        $processedText = '';
        $sthiti1 = 0;
        
        while ($sthiti1 < $textSize) {
            // Calculate the end position for the current chunk
            $sthiti2 = min($sthiti1 + $maxTextSize, $textSize);
            
            // Move $sthiti2 back to the last space within the chunk if needed
            if ($sthiti2 < $textSize) {
                while ($sthiti2 > $sthiti1 && $inputText[$sthiti2] != ' ') {
                    $sthiti2--; 
                }
            }
            
            // Get the current substring for processing
            $modifiedSubstring = substr($inputText, $sthiti1, $sthiti2 - $sthiti1);
            //Log::info("Processing substring: " . $modifiedSubstring);
            // Replace characters using the replaceSymbols method
            $modifiedSubstring = $this->replaceSymbols($modifiedSubstring, $arrayOne, $arrayTwo);
            $modifiedSubstring = trim($modifiedSubstring);

            //Log::info("Modified substring: " . $modifiedSubstring);
            
            // Append the modified substring to the processed text
            $processedText .= $modifiedSubstring;
    
            // Move to the next chunk
            $sthiti1 = $sthiti2 + 1; // Move past the space
        } 
        return $processedText;
    }
    


  

    public function replaceSymbols($modifiedSubstring, $arrayOne, $arrayTwo) {
        // Check if the string is non-blank
        if ($modifiedSubstring !== "") {
            // Define replacements
            $replacements = [
                "त्र्य" => "«य",
                "श्र्य" => "Ü‍‍zय",
                "क़" => "क़",
                "ख़" => "ख़",
                "ग़" => "ग़",
                "ज़" => "ज़",
                "ड़" => "ड़",
                "ढ़" => "ढ़",
                "ऩ" => "ऩ",
                "फ़" => "फ़",
                "य़" => "य़",
                "ऱ" => "ऱ",
            ];
    
            // Log the initial substring
            //Log::info("Initial substring for processing: " . $modifiedSubstring);
    
            $modifiedSubstring = $this->replaceChhoteeEe($modifiedSubstring,$arrayOne, $arrayTwo);
            // Perform initial replacements
            foreach ($replacements as $search => $replace) {
                $modifiedSubstring = str_replace($search, $replace, $modifiedSubstring);
                //Log::info("Replaced '$search' with '$replace': " . $modifiedSubstring);
            } 
          
    
            // Substitute array_two elements in place of corresponding array_one elements
            // for ($i = 0; $i < count($arrayOne); $i++) {
            //     $modifiedSubstring = str_replace($arrayOne[$i], $arrayTwo[$i], $modifiedSubstring);
            // } 
            // $array_one_length = count($arrayOne);
            // if (!empty($modifiedSubstring)) {
            //     for ($input_symbol_idx = 0; $input_symbol_idx < $array_one_length; $input_symbol_idx++) {
            //         // Initialize idx for the while loop
            //         $idx = 0; 
            
            //         while (strpos($modifiedSubstring, $arrayOne[$input_symbol_idx]) !== false) {
            //             $modifiedSubstring = str_replace($arrayOne[$input_symbol_idx], $arrayTwo[$input_symbol_idx], $modifiedSubstring);
            //             // Check if the symbol still exists
            //             $idx = strpos($modifiedSubstring, $arrayOne[$input_symbol_idx]);
            //         }
            //     }
            // }
            

               // Eliminate "र्" and place Z correctly
            // $modifiedSubstring = $this->replaceHalfR($modifiedSubstring);
            // Replace "ि" with "f" and correct its position
            
              
            // Perform additional replacements
            // $modifiedSubstring = $this->performAdditionalReplacements($modifiedSubstring);

           
    
            // Log final result
            //Log::info("Final modified substring after all replacements: " . $modifiedSubstring);
        }
    
        return $modifiedSubstring;
    }
    
    private function replaceChhoteeEe(&$modifiedSubstring, $arrayOne, $arrayTwo) {
        // Code for replacing "ि" (chhotee ee kii maatraa) with "f" and correcting its position too.
        
        $positionOfF = mb_strpos($modifiedSubstring, "ि");
        
        while ($positionOfF !== false) { // while-02
            // Ensure there's a character to the left
            if ($positionOfF > 0) {
                $characterLeftToF = mb_substr($modifiedSubstring, $positionOfF - 1, 1);
                $modifiedSubstring = str_replace($characterLeftToF . "ि", "f" . $characterLeftToF, $modifiedSubstring);
            }
    
            $positionOfF--;
    
            while ($positionOfF > 0 && mb_substr($modifiedSubstring, $positionOfF - 1, 1) == "्") {
                $stringToBeReplaced = mb_substr($modifiedSubstring, $positionOfF - 2, 1) . "्";
                $modifiedSubstring = str_replace($stringToBeReplaced . "f", "f" . $stringToBeReplaced, $modifiedSubstring);
                $positionOfF -= 2;
            }
    
            $positionOfF = mb_strpos($modifiedSubstring, "ि", $positionOfF + 1); // Search for the next occurrence
        }
    
        // Eliminating "र्" and putting Z at the proper position
        $setOfMatras = "ािीुूृेैोौं:ँॅ";
        $modifiedSubstring .= '  '; // Add two spaces after the string
    
        $positionOfHalfR = mb_strpos($modifiedSubstring, "र्");
    
        while ($positionOfHalfR > 0) { // while-03
            // "र्" is two bytes long
            $probablePositionOfZ = $positionOfHalfR + 2;
            $characterAtProbablePositionOfZ = mb_substr($modifiedSubstring, $probablePositionOfZ, 1);
    
            // Trying to find non-matra position right to probablePositionOfZ
            while (mb_strpos($setOfMatras, $characterAtProbablePositionOfZ) !== false) { // while-04
                $probablePositionOfZ++;
                $characterAtProbablePositionOfZ = mb_substr($modifiedSubstring, $probablePositionOfZ, 1);
            }
    
            // Check if the next character is a halant
            $rightToPositionOfZ = $probablePositionOfZ + 1;
    
            if ($rightToPositionOfZ > 0) { // if-03
                $characterRightToPositionOfZ = mb_substr($modifiedSubstring, $rightToPositionOfZ, 1);
    
                while ($characterRightToPositionOfZ == "्") { // while-05
                    $probablePositionOfZ++;
                    $characterAtProbablePositionOfZ = mb_substr($modifiedSubstring, $probablePositionOfZ, 1);
                    $rightToPositionOfZ = $probablePositionOfZ + 1;
                    $characterRightToPositionOfZ = mb_substr($modifiedSubstring, $rightToPositionOfZ, 1);
                }
            }
    
            $stringToBeReplaced = mb_substr($modifiedSubstring, $positionOfHalfR + 2, ($probablePositionOfZ - $positionOfHalfR) - 1);
            $modifiedSubstring = str_replace("र्" . $stringToBeReplaced, $stringToBeReplaced . "Z", $modifiedSubstring);
            
            $positionOfHalfR = mb_strpos($modifiedSubstring, "र्");
        }
    
        // Remove the last two spaces added initially
        $modifiedSubstring = mb_substr($modifiedSubstring, 0, mb_strlen($modifiedSubstring) - 2);
    
        // Substitute array_two elements in place of corresponding array_one elements
        foreach ($arrayOne as $inputSymbolIdx => $symbol) {
            $modifiedSubstring = str_replace($symbol, $arrayTwo[$inputSymbolIdx], $modifiedSubstring);
        }
    
        return $modifiedSubstring;
    }
    
    
    private function performAdditionalReplacements($modifiedSubstring) {
        $modifiedSubstring = str_replace("Zksa", "ksZa", $modifiedSubstring);
        $modifiedSubstring = str_replace("~ Z", "Z~", $modifiedSubstring);
        $modifiedSubstring = str_replace("Zk", "kZ", $modifiedSubstring);
        $modifiedSubstring = str_replace("Zh", "Ê", $modifiedSubstring);
        return $modifiedSubstring;
    }
    

    
    

    public function unicodeToAkruti($inputText)
    {


        // $arrayTwo = [
        //     'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        //     'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        //     '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        //     "~", "!", "@", "#", "_", "^", "&", ":", ";", "'", '"', ",", ".", "$", "¢ ", "£", "¥", "¤", "+", "-", "*", "/", "‰ ", "=", "(", ")", "{", "}", "[", "]", "<", ">", "?", "¿", "¡",  '|', '§', '¶', 'µ', '©',
        //     'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', '×', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'Þ', 'ß',
        //     'à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','÷','ø','ú','û','ü','ù','ý','þ','ÿ'      
        //     ];

        // $arrayOne = [
        //     'र्ीं', 'ँ', 'ण्', 'िङ', 'इ', 'उ', 'प्', 'घ्', 'व्', 'ख्', 'थ्', 'श्', 'ऱ्', 'ध्', 'झ्', 'ैं', 'ीं', 'ए', 'ऊ', 'ळ', 'न्न्', 'ें', 'र्', 'ें', 'भ्', 'र्', 'ैं',
        //     'र्ी', 'ं', 'म्', '्', 'ा', 'ि', 'ु', 'प्', 'ग्', 'र', 'व्', 'त्', 'स्', 'ह', 'द', 'ज्', 'ि', 'ी', 'े', 'ु', 'ल्', 'न्', 'ै', 'र्', 'े', 'ब्', 'र्', 'ै',
        //      '०', '१', '२', '३', '४', '५', '६', '७', '८', '९',
        //      '।', '!', 'ॅ', 'क्ष्', 'ञ्', '्र', 'र्', ':', ';', '’', 'ठ', ',', '.', 'त्र्', 'दृ', '्र', 'र्', 'ं', '॰', '-', 'ङ', '/', '1', 'ृ', '(', ')', 'ढ', 'ल', 'ड', '़', 'ष्', '्न', '?', 'ह्य्', 'ञ्ज्', 'र्ि', 'ु', 'ढ्ढ', 'श्', 'रु',
        //      '्ल', 'द्ब', 'द्ग', 'ा', 'िं', 'ट्ट', 'ठ्ठ', 'ळ्', 'ृ', 'त्त्', 'हृ', 'श्', '्र', 'द्य्', 'ा', 'प्र्', 'द्भ', 'ह्', 'ा', 'प्र्', 'द्ध', 'व्न्', 'ा', 'ह्न', 'ह्र', 'द्व', 'श्र्', 'ॐ',
        // 'द्ध', 'ञ्च्', 'झ्र्', 'ृ', 'ष्ट', '्रू', ' ़', 'ऽ', 'ू', 'श्व्', 'द्र', ':', 'ट', 'छ', '्य्', 'ह्म्', 'स्त्र्', 'ा', 'ा', 'ा', '—', '–', 'ष्ठ', 'ष्', 'ड्ढ', 'ग्र्', 'ज्र्', 'ट्ठ', 'ज्र्', 'ठ', 'ष्ठ'
        //      ];

        // Character mappings
        $arrayOne = [
            //  'र्ण',
          'श्व्','श्व',
          "‘","’","“","”","(",")","{","}","=","।","?","-",
          "µ","॰",",",".", "०","१","२","३","४","५","६","७","८","९",
          "x","+",";","_", "फ़्","क़","ख़","ग़","ज़्","ज़","ड़","ढ़","फ़","य़","ऱ","ऩ", "त्त्","त्त","दृ","कृ", "श्व","ह्न","ह्य","हृ","ह्म","ह्र","ह्","द्द","क्ष्","क्ष","त्र्","त्र","ज्ञ", "छ्य","ट्य","ठ्य","ड्य",
          'ढ्य',"द्य","द्व", "श्र","ट्र","ड्र","ढ्र","छ्र","क्र","फ्र","द्र","प्र","ग्र","रु","रू", "्र", "ओ","औ","आ","अ","ई","इ","उ","ऊ","ऐ","ए","ऋ", "क्","क","क्क","ख्","ख","ग्","ग","घ्","घ","ङ", "चै","च्","च","छ","ज्","ज","झ्","झ","ञ", "ट्ट","ट्ठ","ट","ठ","ड्ड","ड्ढ","ड","ढ्ढ","ढ","ण्","ण", "त्","त","थ्","थ","द्ध","द", "ढ्‌",
          "ध्","ध","न्","न", "प्","प","फ्","फ","ब्","ब","भ्","भ","म्","म", "य्","य","र","ल्","ल","ळ","व्","व", "श्", "श",  "ष्", "ष",  "स्",   "स",    "ह",
          "ऑ","ॉ","ो","ौ","ा","ी","ु","ू","ृ","े","ै", "ं","ँ","ः","ॅ","ऽ","्","्","़","/","ॐ","ढ्ढ",
          "र्ी",

        ];


        $arrayTwo = [
         'é','ée',
        '‘','’','“','”','(',')','{','}','†','~','?','-',
        'µ','¤',',','.','0','1','2','3','4','5','6','7','8','9',
        'x','',';','_',']HedÀ','˜eÀ','™e','œe','','e','›','',']HeÀ',']³e',']j',']ve','Êed','Êe','¢','Je=À','ée','Ú','¿e','Ë','ïe','Û','nd','Î','#','#e','$ed','$e','%e','íîe','ìîe','"îe','[îe',
        '{îe','Ðe','Ü','Þe','ì^','[^','{^','í^','¬eÀ','ÒeÀ','ê','Òe','ûe','©','ª','´','Dees','Deew','Dee','De','F&','F','G','T','Ss','S','$e+','JeÌ','JeÀ','JeÌJeÀ','K','Ke','i','ie','I','Ie','*','®ew','®','®e','í','p','pe','P','Pe','_e','Æ','ù','ì','"','·','ú','[','¶','{','C','Ce','l','le','L','Le','×','o','{d',
        'O','Oe','v','ve','H','He','HeÌ','HeÀ','y','ye','Y','Ye','c','ce','³','³e','j','u','}','U','J','Je','M','Me','<','<e','m','me','n',
        'Dee@','e@','es','ew','e','er','g','t','=','s','w','b','B',':','@','ç','d','d',']','/','ß',
        '&'
        ];        

        // Process input text
        $textSize = strlen($inputText);
        $maxTextSize = 6000; // Maximum chunk size
        $processedText = '';
        $sthiti1 = 0;

        while ($sthiti1 < $textSize) {
            // Calculate the end position for the current chunk
            $sthiti2 = min($sthiti1 + $maxTextSize, $textSize);

            // Move $sthiti2 back to the last space within the chunk if needed
            if ($sthiti2 < $textSize) {
                while ($sthiti2 > $sthiti1 && $inputText[$sthiti2] != ' ') {
                    $sthiti2--;
                }
            }

            // Get the current substring for processing
            $modifiedSubstring = substr($inputText, $sthiti1, $sthiti2 - $sthiti1);

            // Replace characters using the replaceSymbols method
            $modifiedSubstring = $this->replaceSymbolsAkruti($modifiedSubstring, $arrayOne, $arrayTwo);
            $modifiedSubstring = trim($modifiedSubstring);

            // Log::info("Modified substring: " . $modifiedSubstring);

            // Append the modified substring to the processed text
            $processedText .= $modifiedSubstring;
            // Move to the next chunk
            $sthiti1 = $sthiti2 + 1; // Move past the space
        }
        return $processedText;
    }

    public function replaceSymbolsAkruti($modifiedSubstring, $arrayOne, $arrayTwo)
    {
        // Check if the string is non-blank
        if ($modifiedSubstring !== "") {
            // Define replacements
            $replacements = [
                "त्र्य" => "«य",
                "श्र्य" => "Ü‍‍zय",
                "क़" => "क़",
                "ख़" => "ख़",
                "ग़" => "ग़",
                "ज़" => "ज़",
                "ड़" => "ड़",
                "ढ़" => "ढ़",
                "ऩ" => "ऩ",
                "फ़" => "फ़",
                "य़" => "य़",
                "ऱ" => "ऱ",
            ];


            $modifiedSubstring = $this->replaceChhoteeEeAkruti($modifiedSubstring, $arrayOne, $arrayTwo);
            // Perform initial replacements
            foreach ($replacements as $search => $replace) {
                $modifiedSubstring = str_replace($search, $replace, $modifiedSubstring);
            }
            $modifiedSubstring = $this->performAdditionalReplacementsAkruti($modifiedSubstring);

        }

        return $modifiedSubstring;
    }

    private function replaceChhoteeEeAkruti(&$modifiedSubstring, $arrayOne, $arrayTwo)
    {
        // Code for replacing "ि" (chhotee ee kii maatraa) with "f" and correcting its position too.

        $positionOfF = mb_strpos($modifiedSubstring, "ि");
        // Log::info("String to replace : $modifiedSubstring");

        while ($positionOfF !== false) { // while-02
            // Ensure there's a character to the left
            if ($positionOfF > 0) {
                $characterLeftToF = mb_substr($modifiedSubstring, $positionOfF - 1, 1);
                $modifiedSubstring = str_replace($characterLeftToF . "ि", "ef" . $characterLeftToF, $modifiedSubstring);
            }
            // Log::info("String to replace : $modifiedSubstring");
            $positionOfF--;

            while ($positionOfF > 0 && mb_substr($modifiedSubstring, $positionOfF - 1, 1) == "्") {
                $stringToBeReplaced = mb_substr($modifiedSubstring, $positionOfF - 2, 1) . "्";
                $stringToBeReplaced_middle = mb_substr($modifiedSubstring, $positionOfF - 1, 2);
                // if($stringToBeReplaced_middle != 'त्'){
                //     // Log::info("String to replace : $stringToBeReplaced_middle");
                //     $modifiedSubstring = str_replace($stringToBeReplaced . "ef", "eq" . $stringToBeReplaced, $modifiedSubstring);

                // }else{
                //     $modifiedSubstring = str_replace($stringToBeReplaced . "ef", "ef" . $stringToBeReplaced, $modifiedSubstring);
                // }

                $modifiedSubstring = str_replace($stringToBeReplaced . "ef", "ef" . $stringToBeReplaced, $modifiedSubstring);

                // $modifiedSubstring = str_replace($stringToBeReplaced . "ef", "ef" . $stringToBeReplaced, $modifiedSubstring);
                // Log::info($positionOfF);
                $positionOfF -= 2;
            }

            $positionOfF = mb_strpos($modifiedSubstring, "ि", $positionOfF + 1); // Search for the next occurrence
        }

        // Eliminating "र्" and putting Z at the proper position
        $setOfMatras = "ािीुूृेैोौं:ँॅ";
        $modifiedSubstring .= '  '; // Add two spaces after the string

        $positionOfHalfR = mb_strpos($modifiedSubstring, "र्");

        while ($positionOfHalfR > 0) { // while-03
            // "र्" is two bytes long
            $probablePositionOfZ = $positionOfHalfR + 2;
            $characterAtProbablePositionOfZ = mb_substr($modifiedSubstring, $probablePositionOfZ, 1);

            // Trying to find non-matra position right to probablePositionOfZ
            while (mb_strpos($setOfMatras, $characterAtProbablePositionOfZ) !== false) { // while-04
                $probablePositionOfZ++;
                $characterAtProbablePositionOfZ = mb_substr($modifiedSubstring, $probablePositionOfZ, 1);
            }

            // Check if the next character is a halant
            $rightToPositionOfZ = $probablePositionOfZ + 1;

            if ($rightToPositionOfZ > 0) { // if-03
                $characterRightToPositionOfZ = mb_substr($modifiedSubstring, $rightToPositionOfZ, 1);

                while ($characterRightToPositionOfZ == "्") { // while-05
                    $probablePositionOfZ++;
                    $characterAtProbablePositionOfZ = mb_substr($modifiedSubstring, $probablePositionOfZ, 1);
                    $rightToPositionOfZ = $probablePositionOfZ + 1;
                    $characterRightToPositionOfZ = mb_substr($modifiedSubstring, $rightToPositionOfZ, 1);
                }


            }
            $stringToBeReplaced = mb_substr($modifiedSubstring, $positionOfHalfR + 2, ($probablePositionOfZ - $positionOfHalfR) - 1);

            $modifiedSubstring = str_replace("र्" . $stringToBeReplaced, $stringToBeReplaced . "Z", $modifiedSubstring);

            $positionOfHalfR = mb_strpos($modifiedSubstring, "र्");
        }

        // Remove the last two spaces added initially
        $modifiedSubstring = mb_substr($modifiedSubstring, 0, mb_strlen($modifiedSubstring) - 2);

        // Substitute array_two elements in place of corresponding array_one elements
        foreach ($arrayOne as $inputSymbolIdx => $symbol) {

            $modifiedSubstring = str_replace($symbol, $arrayTwo[$inputSymbolIdx], $modifiedSubstring);
            if (str_replace($symbol, $arrayTwo[$inputSymbolIdx], $modifiedSubstring)) {
                // Log::info(" '$symbol' with '{$arrayTwo[$inputSymbolIdx]}': " . $modifiedSubstring);
            } 
        }

        return $modifiedSubstring;
    }

    private function performAdditionalReplacementsAkruti($modifiedSubstring)
    {

        $modifiedSubstring = str_replace("d Z", "&d", $modifiedSubstring);
        $modifiedSubstring = str_replace("Ze", "e&", $modifiedSubstring);
        $modifiedSubstring = str_replace("Z", "&", $modifiedSubstring);
        $modifiedSubstring = str_replace("À&s", "&sÀ", $modifiedSubstring);
        $modifiedSubstring = str_replace("JeÀg", "JegÀ", $modifiedSubstring); 
        $modifiedSubstring = str_replace("e&r", "era", $modifiedSubstring);

        return $modifiedSubstring;
    }
    

}