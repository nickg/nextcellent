
//Call Qunit if enabled.
jQuery("document").ready(function(){
	//Qunit conditional call
	 if (ngg_get_url_vars().nextcellent) { //Call qunit only if there is an url parameter nextcellent=true
        nxc_test.runTests();               //check nxc.test.js file to see test there!
    }
});

//get url parameter list, return array with parameter names and values.
function ngg_get_url_vars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
