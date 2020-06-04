const SEARCH_REQUEST = 'search';
const ID_REQUEST = 'id';
const ALL_REQUEST = 'all';

/*Helper Functions*/
const _id = (id="") => {
    if(id == ""){return false;}
    return document.getElementById(id);
}

const handleResponse = (jsonResponse, requestType) =>
{
    var json = JSON.parse(jsonResponse)
    if(requestType == SEARCH_REQUEST || requestType == ALL_REQUEST) {
        if(json.success == 'false') {
            _id('spinner-cover').style.display = "none";
            _id('error').innerHTML=json.reply;
            $('#spinner-cover').slideUp(200, function() {
                _id('error').style.display = "block";
            });        }
        if(json.success == 'true') {
            $('#spinner-cover').slideUp(200, function() {
                _id('table-cover').innerHTML = json.reply;
                $('#table-cover').slideDown(200);
            });
        }
        return;
    }
    if(requestType == ID_REQUEST) {
        if (json.success == 'true') {
            console.log(json);
            // fieldValues = Object.keys(json.reply)
            for (let [key, value] of Object.entries(json.reply)) {
                _id(key).innerHTML = value
                console.log(`${key}: ${value}`);
                $('#modal-spinner-cover').slideUp(500, function() {
                    $('#user-contents').slideDown(500);
                });
            }
            // console.log(fieldValues);
            // for (var key in fieldValues) {
            //     _id(key).innerHTML = fieldValues[key]
            //     console.log(key, value);

            // }
        }
        if (json.success == 'false') {
            console.log("False Request");
            _id('modal-error').innerHTML = json.reply;
            $('#modal-spinner-cover').slideUp(500, function() {
                _id('modal-error').style.display = "block";
            });
        }
    }

}


/*
Request Function that Handles all Request to Server
Three types of Request
1. All:   Loads all Users
2. Id:    Loads a User By their Id
3. search: Search all Users by a particular fied
*/
const request = (requestType, param="", column='') =>
{
    let data = new Object();
    data.nonce = nonce;
    data.action = 'inpuserparser_hook';
    switch(requestType) {
        case ALL_REQUEST:
            data.requestType = 'all';
            _id('spinner-cover').style.display = "block";
            _id('table-cover').style.display = "block";
            break;
        case ID_REQUEST:
            data.requestType = 'id';
            data.id=param;
            break;
        case SEARCH_REQUEST:
            data.requestType = 'search';
            _id('spinner-cover').style.display = "block";
            _id('table-cover').style.display = "block";
            data.searchStr = param;
            data.column = column;
            break;
        default:
            return;
    }
    if((requestType == ID_REQUEST || requestType == SEARCH_REQUEST) && param == ""){
        console.log("Param is Null");
        return;
    }
    if(requestType == SEARCH_REQUEST && column==''){
        console.log("Column is Null");
        return;
    }
    console.log("Requestong...");
    console.log(ajax_url);

    $.ajax({
        type: "POST",
        url: ajax_url,
        data: data,
        timeout: 6000,
        success: function(response){
            console.log(response);
            handleResponse(response, requestType);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if(textStatus==="timeout") {
                handleResponse('{"success":"false","reply":"Request Time Out"}', requestType);
            }
        }});
}

const showDetail = (id) =>
{
    request(ID_REQUEST, id);
    _id('details-activate').click();
    _id('user-contents').style.display = "none";
    _id('modal-error').style.display = "none";
    _id('modal-spinner-cover').style.display = "block";
}

const searchRequest = () =>
{
    let searchStr = (_id("search-input").value);
    _id('search-info').innerHTML = "";
    _id('table-cover').innerHTML= "";

    if(searchStr.length < 1){
        request(ALL_REQUEST);
        return;
    }

    if(_id("select_options").value != 'id'){
        if(searchStr.length < 3) {
            _id('search-info').innerHTML = `${3-searchStr.length} more Char.. (At least 3 Characters)`;
            _id('search-info').style.display = "block";
            _id('spinner-cover').style.display = "none";
            return;
        }
    }
    request(SEARCH_REQUEST, searchStr, _id("select_options").value);
}

const setSearchBy = () =>
{
    let sel = _id("select_options")
    let opt = sel.options[sel.selectedIndex];
    let value = opt.text;
    _id("search-input").setAttribute('placeholder', `Search By ${value}`);
    searchRequest();
}


window.addEventListener('load', (event) => {
    if(_id("select_options") != null) {
        _id("select_options").addEventListener("change", setSearchBy);
        _id("search-input").addEventListener("input", searchRequest);
        setSearchBy();
    }

    request(ALL_REQUEST);

});
