<?php
$_locale = "hi_IN";
$language = $_locale.".utf8";
putenv("LANGUAGE = $language");
setlocale(LC_ALL, $language);

$_languageDomain = 'internal_messages';
bindtextdomain($_languageDomain, '../../Locale');
bind_textdomain_codeset($_languageDomain, 'UTF-8');
textdomain($_languageDomain);
//("Newsroom :") समाचार कक्ष  :  निर्मित उपयोगकर्ता आईडी एक प्राकृतिक नंबर होना  चाहिए !
echo ("Entn_Model_".gettext("Newsroom :").gettext("createdUserId should be a natural number!")); echo "<br>";
//ग्राहक आईडी एक प्राकृतिक नंबर होना  चाहिए
gettext("clientId should be a natural number!");echo "<br>";
//जाँच प्रविष्टि / प्रविष्टियाँ हटा दी गयी  है
$success_message = gettext("Checked entry/entries has been deleted");
//समाचार कक्ष नाम एक आवश्यक मापदण्ड है और खाली नहीं कर सकते
echo(gettext("newsroom name is a required parameter and cannot be blank!")); echo "<br>";
// निर्मित उपयोगकर्ता आईडी एक प्राकृतिक नंबर होना  चाहिए ! 
echo(gettext("createdUserId should be a natural number!")); echo "<br>";
//समाचार कक्ष नाम पैहले से मौज़ुद है
echo(gettext("This newsroom name already exists")); echo "<br>";
//समाचार कक्ष आईडी एक प्राकृतिक नंबर होना  चाहिए ! 
echo(gettext("newsroomId should be a natural number!")); echo "<br>";
//यह  दस्तावेज नष्ट नहीं किया जा सकता क्युंकी लेख फाइल में मौज़ुद है 
echo(gettext("This record can't deleted because entry exist in Dossier.")); echo "<br>";
//यह  दस्तावेज नष्ट नहीं किया जा सकता क्युंकी लेख समाचार कक्ष लेखन-आधार में मौज़ुद है
echo(gettext("This record can't deleted because entry exist in Newsroom Desk.")); echo "<br>";
//यह  दस्तावेज नष्ट नहीं किया जा सकता क्युंकी लेख समाचार कक्ष प्रकाशन में मौज़ुद है
echo(gettext("This record can't deleted because entry exist in Newsroom Publication.")); echo "<br>";
echo $success_message; echo "<br>"; 
//वर्तमान में कोई आँकड़े उपलब्ध नहीं है
echo(gettext("Currently there is no data available.")); echo "<br>";
//आप इस पृष्ठ के उपयोग के लिए अधिकृत  नहीं हैं. कृपया व्यवस्थापक से सलाह ले
echo(gettext("You are not authorized to access this page. Please consult admin.")); echo "<br>"; 
?>
