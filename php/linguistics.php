<?php

/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
// The file linguistics contains tools for linguistical analysis

// global variables
$is_noun = false;
$acronym = 99999;
$value_separate = 3;
$value_glue = 2;

// phpSyllable: include and prepare
require_once("../phpSyllable-master" . '/classes/autoloader.php');
$phpSyllable_dictionary = "de";                             //"de_CH";
$syllable = new Syllable($phpSyllable_dictionary);          // 'en-us'
$syllable->setHyphen(new Syllable_Hyphen_Dash());           // get all syllables, with a dash

// hunspell: dictionary
$hunspell_dictionary = "de_CH"; //"de_DE"; //"de_CH";

// functions




// ***************************************************** unused code **********************************
// pspell: dictionary
$pspell_dictionary = "de";
//$pspell_link = pspell_new("$pspell_dictionary", "", "", "utf-8");

// functions
function capitalize($word) {
    //$word[0] = mb_strtoupper($word[0], "UTF-8");
    $first = mb_substr($word, 0, 1);
    $rest = mb_substr($word, 1);
    $word = mb_strtoupper($first, "UTF-8") . $rest; // always those twisted solutions to solve the annoying UTF-8 problem ... (slow, but I don't see any other possibility for the moment ...)
    return $word;
}
function array2capitalizedStringList($array) {
    $word_list = "";
    for ($i=0; $i<count($array); $i++) {
        $result = capitalize($array[$i]);
        $word_list .= $result . " ";
    }
    return $word_list;
}
function decapitalize($word) {
    //$word[0] = mb_strtolower($word[0], "UTF-8"); // wrong for umlauts
    $word = mb_strtolower($word, "UTF-8"); // slower but with utf-8
    return $word;
}
function hyphenate($word) {
    global $syllable;
    //echo "word: $word<br>";
    return preg_replace("/-([a-zA-Z])-/", "$1-", $syllable->hyphenateText($word)); // quick fix: add orphanated chars to preceeding (phpSyllable produces such erroneous outputs ... !?)
}
function word2array($word) {
    return explode("-", $word);
}
function composedWordsArray2hyphenatedString($array) {
    $output = "";
    for ($i=0; $i<count($array); $i++) {
        if ($i!=0) $output .= decapitalize($array[$i]);
        else $output .= $array[$i];
        if ($i<count($array)-1) $output .= "\\";
    }
    return $output;
}
function PSPELLcapitalizedStringList2composedWordsArray($string) {
    //global $pspell_link; // produces unpredictable results if link is declared only once and reused as global?!?
    global $pspell_dictionary;
    $pspell_link = pspell_new("$pspell_dictionary", "", "", "utf-8"); // this seems to work, but us probably slower ... ?!?

    $composed_words = array();
    $word_list_array = explode(" ", $string);
    //var_dump($word_list_array);
    for ($i=0; $i<count($word_list_array); $i++) {
        $test_in_dictionary = $word_list_array[$i];
        for ($j=$i; $j<count($word_list_array); $j++) {
            $hit = false;
            if ($j!=$i) $test_in_dictionary .= decapitalize($word_list_array[$j]);
            if (mb_strlen($test_in_dictionary)>2) {
                //echo "<br>test: i/j: $i/$j: $test_in_dictionary<br>";
                //echo "result hunspell: "; // . $o[1] . "<br>";
                //var_dump($o);
                //echo "<br>";
                // check for Fugen-s!
                //echo "pspell($test_in_dictionary): >" . pspell_check($pspell_link, $test_in_dictionary) . "<<br>"; 
                if (pspell_check($pspell_link, $test_in_dictionary) != false) {
                    //echo "Word: $test_in_dictionary found in dictionary!<br>";
                    $hit = true;
                    $composed_words[] = $test_in_dictionary;
                    $i = $j;
                    $j = count($word_list_array);
                    break;
                }
            }
        }
        if (!$hit) {
            $combine_with_preceeding = $composed_words[count($composed_words)-1] . decapitalize($word_list_array[$i]);
            //echo "additional check: combine with preceeding = $combine_with_preceeding<br>";
            if ($i<count($word_list_array)-1) {
                if (pspell_check($pspell_link, $combine_with_preceeding) != false) {
                    //echo "<br><br>i=$i<br><br>";
                    $composed_words[count($composed_words)-1] .= decapitalize($word_list_array[$i]);
                } else $composed_words[] = $word_list_array[$i];
            } 
        }
    }
    return $composed_words;
}
function capitalizedStringList2composedWordsArray($string) {
    global $hunspell_dictionary;
    $composed_words = array();
    $word_list_array = explode(" ", $string);
    //var_dump($word_list_array);
    $length = count($word_list_array);
    for ($i=0; $i<$length; $i++) {
        $test_in_dictionary = $word_list_array[$i];
        for ($j=$i; $j<$length; $j++) {
            $hit = false;
            if ($j!=$i) $test_in_dictionary .= decapitalize($word_list_array[$j]);
            if (mb_strlen($test_in_dictionary)>2) {
                //echo "<br>test: i/j: $i/$j: $test_in_dictionary<br>";
                exec("echo \"$test_in_dictionary\" | hunspell -d $hunspell_dictionary -a -m -s", $o); // assign output to $o (= array)
                //echo "result hunspell: "; // . $o[1] . "<br>";
                //var_dump($o);
                //echo "<br>";
                // check for Fugen-s!
                if (($o[count($o)-2][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$test_in_dictionary-") > 0))) {
                    //echo "Word: $test_in_dictionary found in dictionary!<br>";
                    $hit = true;
                    $composed_words[] = $test_in_dictionary;
                    $i = $j;
                    $j = $length; //count($word_list_array);
                    break;
                }
            }
        }
        if (!$hit) {
            $combine_with_preceeding = $composed_words[count($composed_words)-1] . decapitalize($word_list_array[$i]);
            //echo "additional check: combine with preceeding = $combine_with_preceeding<br>";
            exec("echo \"$combine_with_preceeding\" | hunspell -d $hunspell_dictionary -a -m -s", $o, $v); // assign output to $o (= array)
            //var_dump($o);
            if ($i<count($word_list_array)-1) {
                if (($o[count($o)-2][0] === "*") || ($o[4][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$combine_with_preceeding-") > 0))) {
                    //echo "<br><br>i=$i<br><br>";
                    //echo "combine with preceeding ...<br>";
                    $composed_words[count($composed_words)-1] .= decapitalize($word_list_array[$i]);
                } else {
                    //echo "consider it a separate word!<br>";
                    $composed_words[] = $word_list_array[$i];
                }
            } 
        }
    }
    return $composed_words;
}

function analyze_composed_words_and_hyphenate($word, $speller) {
    $word = hyphenate($word);
    $word_array = word2array($word);
    $word_list = array2capitalizedStringList($word_array);
    switch ($speller) {
        case "hunspell" : $composed_words = capitalizedStringList2composedWordsArray($word_list); break; // better!
        case "pspell" : $composed_words = PSPELLcapitalizedStringList2composedWordsArray($word_list); break; // just in case hunspell isn't available
    }
    $final_result = composedWordsArray2hyphenatedString($composed_words);
    $final_result_hyphenated = hyphenate($final_result);
    return $final_result_hyphenated;
}

// the above function analyze_composed_words_and_hyphenate works quite well, but it is terribly slow
// this is due to many and slow shell calls ... the goal therefore is to make it faster by grouping
// the words that have to be tested and to call hunspell only once per word.
// this can be achieved creating a list of all possible syllable combinations.
// For examples: Schreib-tisch-tä-ter = 4 Syllables (ABCD)
// Combinations: 
// 1 syllable: A B C D = 4
// 2 syllables: AB BC CD = 3
// 3 syllables: ABC BCD CDE = 2
// 4 syllables: ABCD = 1 (this one, if correctly spelled, should always return * from hunspell)
// total: (n+1) * (n/2) = 10 combinations 
// Since parts of words sometimes can only be recognized by hunspell when they go with an dash in 
// the end, this Variant must also be tested (example: Versicherungsvertreter => Versicherungs
// only returns * if tested as Versicherungs-)
// This doubles the possibilities: total = (n+1) * n, so:
// 1 syllable => 2 possibilities
// 2 syllables => 6 possibilities
// 3 syllables => 12 possibilities
// 4 syllables => 20 possibilities
// 5 syllables => 30 possibilities
// 6 syllables => 42 possibilities
// 7 syllables => 56 possibilities
// The first step therefore is to call hunspell with all these possibilities, for example:
// echo "Schreib Tisch Tä Ter Schreibtisch Tischtä Täter Schreibtischtä Tischtäter Schreibtischtäter
// Schreib- Tisch- Tä- Ter- Schreibtisch- Tischtä- Täter- Schreibtischtä- Tischtäter- Schreibtischtäter-" | hunspell -d de_CD -a
// In a second step the algorithm must check, what combinations where recognized as correct
// (= possible) and which combination of these combinations is the most adequate.
// The tricky part: Not all combinations recognized by hunspell will from a word inside
// the composed word! For example: Was-ser-schloss => "Was" will be recognized as a correct
// german word; nonetheless it isn't a base word composing Wasserschloss since the second
// part "ser" would stay "orphanized". Therefore the algorithm must find the best combinations
// (without orphanized parts).

//$array = array(); // almost no performace gain if $array is declared as global variable!
function count_uppercase($string) {
    global $acronym;
    $stripped = preg_replace("/[A-ZÄÖÜ]/u", "", $string); // umlaut untested! => Umlaut needs the u-modifier in REGEX!
    //echo "string: >$string<<br>stripped: >$stripped<<br>";
    $length1 = mb_strlen($string);
    $length2 = mb_strlen($stripped);
    //echo "length1: $length1<br>length2: $length2<br>";
    $difference = $length1 - $length2;
    //echo "difference: $difference<br>";
    if ($length2 === 0) return $acronym;
    else return $difference;
}

function analyze_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes) {
    $several_words = explode("-", $word);  // if word contains - => split it into array
    $result = "";
    //echo "stems: $stems<br>";
    //echo "suffixes: $suffixes<br>";
    for ($i=0;$i<count($several_words);$i++) {
        $single_result = analyze_one_word_linguistically($several_words[$i], $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes);
        //echo "single result: $single_result<br>";
       
        $result .= ($i==0) ? $single_result : "=" . $single_result;     // rearrange complete word using = instead of - (since - is used for syllables)
    }
    //echo "result: $result<br>";
    if ($result === "Array") {
        if ($_SESSION['hyphenate_yesno']) return hyphenate($word);    // if word isn't found in dictionary, string "Array" is returned => why?! This is just a quick fix to prevent wrong results
        else return $word;
    } else {
        $result = mark_affixes($result, $prefixes, $suffixes);
        //echo "result: $result<br>";
        return $result;
    }
}
    
function mark_prefixes($word, $prefixes) {
    // word: linguistically analyzed word (hyphenated and containing composed words and prefixes separated by |
    // prefixes: prefix list => goal is to mark prefixes with an + instead of | like "ge|laufen" => "ge+laufen"
    $prefix_list = explode(",", $prefixes);
    for ($i=0; $i<count($prefix_list); $i++) {
        $actual_prefix = trim($prefix_list[$i]);
        //echo "prefix: $actual_prefix word: $word<br>";
        $word = preg_replace("/(^|\+|\|)($actual_prefix)\|/i", "$1$2+", $word); // i = regex caseless modifier
        //echo "result: $word<br>";
    }
    return $word;
}

function mark_suffixes($word, $suffixes) {
    // word: linguistically analyzed word (hyphenated and containing composed words and prefixes separated by |
    // prefixes: prefix list => goal is to mark prefixes with an + instead of | like "ge|laufen" => "ge+laufen"
    $suffix_list = explode(",", $suffixes);
    for ($i=0; $i<count($suffix_list); $i++) {
        $actual_suffix = trim($suffix_list[$i]);
        //echo "prefix: $actual_prefix word: $word<br>";
        $word = preg_replace("/(-|\|)($actual_suffix)($|\|)/i", "#$2$3", $word); // i = regex caseless modifier
        //echo "result: $word<br>";
    }
    return $word;
}

function mark_affixes($word, $prefixes, $suffixes) {
    $word = mark_prefixes($word, $prefixes);
    $word = mark_suffixes($word, $suffixes);
    return $word;
}

function analyze_one_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes) {
    //echo "analyze: hyphenate: $hyphenate decompose: $decompose separate: $separate glue: $glue<br>";
    
    // $separate: if length of composed word < $separate => use | (otherwise use \ and separate composed word)
    //            if 0: separate always
    // $glue: if length of composed word < $glue => use - (= syllable of same word), otherwise use | or \
    //        if 0: glue always (= annulate effect of linguistical analysis)
    // Examples: 
    // a) $glue = 4:                                    $glue = 0:
    //    Eu-len\spie\gel => Eu-len\spie-gel            Eu-len-spie-gel
    //    Ab\tei-lungs\lei-ter => Ab-teilungs\leiter
    // b) $separate = 4:                $separate = 0:
    //    Mut\pro-be => Mut|probe       Mut\pro-be
    //    Ha-sen\fuss => Hasen\fuss     Ha-sen\fuss
    // declare globals
    global $is_noun;    // true if first letter of word is a capital
    global $acronym, $value_separate, $value_glue, $value_hyphenate;
    // set globals
    $value_separate = $separate;
    $value_glue = $glue;
    $value_hyphenate = $hyphenate;
    //echo "suffixes (one word): $suffixes<br>";
    
    // check for acronyms and nouns
    $upper_case = count_uppercase($word);
    if ($upper_case === $acronym) return $word;         // return word without modifications if it is an acronym (= upper case only)
    elseif ($upper_case > 1) return hyphenate($word);   // probably an acronym with some lower case => hyphenate        
    else {
    
        if ($decompose) {
            //echo "decompose word<br>";
            list($word_list_as_string, $array) = create_word_list($word);
            //echo "stems: $stems<br>";
            //echo "suffixes (one word): $suffixes<br>";
   
            $array = eliminate_inexistent_words_from_array($word_list_as_string, $array, $prefixes, $stems, $suffixes);
            //var_dump($array);
            $result = recursive_search(0,0, $array);
            
            //echo "inside (one word): word: $word result: $result<br>";
            if ($result === "") $result = $word; // fix bug: recursive search can return "" instead of a word if word isn't found in hunspell dictionary
        } else $result = $word; //$result = iconv(mb_detect_encoding($word, mb_detect_order(), true), "UTF-8", $word);
        //echo "$result - $word<br>";
        if ($hyphenate) $result = hyphenate($result);
        if ($upper_case === 1) {
            //echo "word is noun<br>";
            //echo "1:$result<br>";
            $result = mb_strtolower($result, "UTF-8"); // argh ... always these encoding troubles ...
            //echo "2:$result<br>";
            $result = capitalize($result);
            //echo "3:$result<br>";
           
        } else $result = mb_strtolower($result);
        return $result;
    }
}

function eliminate_inexistent_words_from_array($string, $array, $prefixes, $stems, $suffixes) {
    $shell_command = /* escapeshellcmd( */"echo \"$string\" | hunspell -i utf-8 -d de_CH -a" /* ) */;
    // explode strings to get rid of commas
    $prefixes_array = explode(",", $prefixes);
    $stems_array = explode(",", $stems);
    $suffixes_array = explode(",", $suffixes);
    // implode to add spaces for string comparison
    $prefixes = " " . implode(" ", $prefixes_array) . " ";
    $stems = " " . implode(" ", $stems_array) . " ";
    $suffixes = " " . implode(" ", $suffixes_array) . " ";
    //echo "<br>suffixes(eliminate): $suffixes<br>";
    
    //echo "$shell_command<br>";
    //echo "hunspell: ";
    exec("$shell_command",$o);
    //var_dump($o);
    $length = count($array[0]);
    $offset = 1;
    for ($l=0;$l<$length; $l++) {
        for ($r=0;$r<count($array[$l]); $r++) {
            //echo "<br>result: " . $array[$l][$r][0] . ": >" . $o[$offset] . "<<br>";
            //echo "prefix test: $prefixes: " . mb_strpos(mb_strtolower($prefixes), mb_strtolower($array[$l][$r][0])) . "<br>";
            //echo "stem test: $stems: " . mb_strpos(mb_strtolower($stems), mb_strtolower($array[$l][$r][0])) . "<br>";
            
            if (($o[$offset] === "*") || (($o[$offset][0] === "&") && (mb_strpos($o[$offset], $array[$l][$r][0] . "-") != false))) {
                //echo "match * found: " . $array[$l][$r][0] . "<br>";
                
            } elseif (mb_strpos(mb_strtolower($prefixes), " " . mb_strtolower($array[$l][$r][0]) . " ") !== false) { 
                // if word is in prefix list => separate it as if it where a word!
                //echo "word: " . $array[$l][$r][0] . " is a prefix!<br>";
                // do nothing (leave word in array)
            } elseif (mb_strpos(mb_strtolower($stems), mb_strtolower(" " . $array[$l][$r][0]) . " ") !== false) {
                //echo "part " . $array[$l][$r][0] . " is a valid (irregular) stem!<br>";
                
            } elseif (mb_strpos(mb_strtolower($suffixes), mb_strtolower(" " . $array[$l][$r][0]) . " ") !== false) {
                //echo "part " . $array[$l][$r][0] . " is a valid suffix!<br>";
                
            } else {
                // no match => delete string in array (use same data field for performance reason)
                //echo "no match: " . $array[$l][$r][0] . "<br>";
                $array[$l][$r][0] = ""; // "" means: no match!
                
            }
            $offset+=1;
        }
    }
    //echo "<br><br>array:<br>";
    //var_dump($array);
    return $array;
}

function create_word_list($word) {
    //global $array; // don't treat $word_list_as_array as function value but as global variable for performance reason
    $hyphenated = hyphenate($word);
    //echo "$hyphenated<br>";
    $hyphenated = decapitalize($hyphenated);
    //echo "$hyphenated<br>";
    
    $hyphenated_array = explode("-", $hyphenated);
    $word_list_as_string = "";
    $array = array();
    $syllables_count = count($hyphenated_array);
    for ($l=0; $l<$syllables_count; $l++) { // l = line of combinations
        for ($r=0; $r<$syllables_count-$l; $r++) {  // r = row of combinations
            $single = "";
            for ($n=0; $n<$l+1; $n++) {     // n = length of combination
                $single .= $hyphenated_array[$r+$n];
            }
            $single = capitalize($single);
            //$single_plus_dash = "$single-";
            //$word_list_as_string .= "$single $single_plus_dash ";
            $word_list_as_string .= "$single ";
            $array[$l][$r][0] = $single;
            //$word_list_as_array[$l][$r][1] = $single_plus_dash; // don't create dash-list for better performance
        }
    }
    return array($word_list_as_string, $array);
    //return $word_list_as_string; // return only string for performance reason => almost no gain: revert back to function parameters
}

function recursive_search($line, $row, $array) {
    global $value_glue, $value_separate;
    //var_dump($array);
    //global $array;
    //echo "call ($line/$row): " . $array[$line][$row][0] . " (" . $array[$line][$row][2] . ")<br>";
    //if (($line < 0) || ($row < 0) || ($line > count($array)) || ($row > count($array[$line]))) return "";
    if ($array[$line][$row][0] != "") {
        //echo "that's a good start: word exists!<br>";
        if ($row === count($array[$line])-1) {
            //echo "reached end of line => return >" . $array[$line][$row][0] . "<<br>";
            //$hit = true;
            return $array[$line][$row][0];
        } else {
            $temp_row = $line+$row+1;
            $temp_line = 0; //count($array) - $temp_row-1; // could this do horizontal as well?!
            //if (($line-1-$row>=0) && ($row+$line<count($array[$line-1-$row]))) {
            if (($temp_line>=0) && ($temp_row<count($array[$temp_line]))) {
                //echo "=> try up<br>";
                //$up = recursive_search($line-1, $row+$line, $array);
                $up = recursive_search($temp_line, $temp_row, $array);
            } else $up = "";
            if (mb_strlen($up)>0) {
                //echo "found up: $up and return my own: " . $array[$line][$row][0] . "<br>";
                $length_candidate = mb_strlen($array[$line][$row][0]);
                $pos1 = mb_strpos($up, "|"); // can return boolean or int!!!
                $pos2 = mb_strpos($up, "\\");
                if (($pos1 === false) && ($pos2 === false)) {
                    $pos3 = mb_strlen($up); // if no | and \ => take strlen of already existing word
                    $pos1 = 99999;
                    $pos2 = 99999;
                } else $pos3 = 99999;
                if ($pos1 != 99999) $relevant = $pos1;
                if ($pos2 != 99999) $relevant = $pos2;
                if ($pos3 != 99999) $relevant = $pos3;
                if (($length_candidate > $value_glue) && ($relevant > $value_glue)) {
                   
                    //echo "candidate: " . $array[$line][$row][0] . " up: $up pos123relevant: >$pos1-$pos2-$pos3-$relevant<<br>";
                    
                    if (($length_candidate > $value_separate) || ($relevant > $value_separate)) return $array[$line][$row][0] . "\\" . $up;
                    else return $array[$line][$row][0] . "|" . $up;
                } else return $array[$line][$row][0] . "|" . $up;
            } else /* {
                /*if ($row+$line+1<count($array[$line])) {
                    echo "=> try horizontal<br>";
                    $horizontal = recursive_search($line, $row+$line+1, $array);
                } else $horizontal = "";
                if (mb_strlen($horizontal)>0) {
                    echo "found horizontal: $horizontal and return my own: " . $array[$line][$row][0] . "<br>";
                    return $array[$line][$row][0] . "\\" .$horizontal;
                } else */ {
                    if (($line+1<count($array)) && ($row<count($array[$line+1]))) {
                        //echo "=> try down (count(array)=" . count($array) . "/count(array(line))=" . count($array[$line]) . ")<br>";
                        $down = recursive_search($line+1, $row, $array);
                    } else $down = "";
                    if (mb_strlen($down)>0) {
                        //echo "found down: $down (don't return own " . $array[$line][$row][0] . "<br>";
                        return /*$array[$line][$row][0] . "\\" . */ $down;
                    } else return ""; // no luck - even the main word isn't recognized by hunspell ...
               //}
            }
        }
    } else {
        if (($line+1<count($array)) && ($row<count($array[$line+1]))) {
            //echo "no luck => traverse down<br>"; 
            if ($line+1<count($array)) return recursive_search($line+1, $row, $array);
        } else {
            //echo "no luck => end traversing (go back)<br>";
            return "";
        }
    }
}

/********************************** some tests with hunspell spellchecker executed via shell
//echo shell_exec(escapeshellarg("echo \"Testwort\" \| hunspell -d de_CH -a"));
//echo shell_exec(escapeshellarg("hunspell -d de_CH -f \"testwoerter.txt\""));
//echo "Schiff=fahrts=mu=se=um" | hunspell -d de_CH -a
//echo escapeshellarg('echo "test" | hunspell -d de_CH -a');
echo $safe_mode;

//echo htmlspecialchars(shell_exec("echo 'Schifffahrt' > hello.txt"));
//echo htmlspecialchars(shell_exec("cat hello.txt"));

//echo "<br>";
//echo htmlspecialchars(system(escapeshellcmd("hunspell -d de_CH -a -f hello.txt")));

//echo exec("hunspell -d de_CH -a -f hello.txt", $o); // assay output to $o (= array)
$word = "Voruntersuchung Zweitwort Drittwort"; //"Schifffahrt";
$dictionary = "de_DE"; //"de_CH";

// check for existing dictionaries
echo system("hunspell -D -a", $o); // assay output to $o (= array)
var_dump($o);
echo "<br>";
*/

/*
*/


/******************************************* another spellchecker test with pspell */
/*
$pspell_link = pspell_new("de");

if (pspell_check($pspell_link, "Schiff-fahrt")) {
    echo "This is a valid spelling";
} else {
    echo "Sorry, wrong spelling";
}
*/

?>