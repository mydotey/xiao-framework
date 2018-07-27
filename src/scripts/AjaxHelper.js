function AjaxHelper(params)
{
    if(params === undefined)
        params = {};
    var url = params.url === undefined ? "" : params.url;
    var data = params.data === undefined ? {HTTP_X_REQUESTED_WITH: true} : params.data;
    var method = params.method === undefined ? "GET" : params.method;
    var callback = params.callback === undefined ? null : params.callback;

    this.send = function(params){
        if(params === undefined)
        {
            params = {};
        }
        if(params.url !== undefined)
            url = params.url;
        if(params.data !== undefined)
            data = params.data;
        if(params.method !== undefined)
            method = params.method;
        if(params.callback !== undefined)
            callback = params.callback;

        var queryString = "";
        for(var key in data)
        {
            if(queryString !== "")
                queryString += "&";
            queryString += key + "=" + encodeURIComponent(data[key]);
        }

        var requestUrl = url;
        if(method == "GET")
        {
            if(url.indexOf("?") == -1)
                requestUrl += "?";
            else
                requestUrl += "&";
            requestUrl += queryString;
        }

        var xmlHttpRequest = null;
        try
        {
            xmlHttpRequest = new XMLHttpRequest();
        }
        catch(ex)
        {
            xmlHttpRequest = new ActiveXObject("Microsoft.XMLHttp");
        }
        xmlHttpRequest.open(method, requestUrl, true);
        xmlHttpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlHttpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        if(callback !== null)
        {
            xmlHttpRequest.onreadystatechange = function(){
                if(xmlHttpRequest.readyState == 4 && xmlHttpRequest.status == 200)
                {
                    callback({xml: xmlHttpRequest.responseXML, text: xmlHttpRequest.responseText});
                }
            };
        }

        var requestData = method === "GET" ? null : queryString;
        xmlHttpRequest.send(requestData);
    };
}
