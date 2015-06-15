var url = require('system').args[1];

var header = new String("header");
var body = new String("1");
var page = require('webpage').create();


/*page.onResourceReceived = function(response) {
 if(header == "") {
 header = "###begin header###" + JSON.stringify(response) + '###end header###';
 }
 };*/


page.open(url, function (status) {
    body = "###begin body###" + page.content + '###end body######begin status###' + status + '###end status###';
    phantom.exit();
});

while (true) {

    console.log(body);
    if (body != "" && header != "") {
        console.log(body);
        console.log(header);
        phantom.exit();
    }
}