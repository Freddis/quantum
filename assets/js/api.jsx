class Api {

    #get = function (url, params, callback, error) {
        let urlObj = new URL(window.location.origin + url);
        Object.keys(params).forEach(key => urlObj.searchParams.append(key, params[key]));
        this.#fetch(urlObj, {}, callback, error);
    };

    #fetch = function (url, params, callback, error) {

        fetch(url, params).then(response => response.json()).then(json => {
            if (!json || json.status === undefined || !json.data) {
                console.log(json);
                return error("Invalid response format");
            }
            if (json.status === 0) {
                console.log("error");
                return error(json.data[0]);
            }
            callback(json.data);
        }).catch(e => {
            console.log("rejected");
            error(e);
        });
    };

    #post = function (url, params, callback, error) {
        let urlObj = new URL(window.location.origin + url);
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(params)
        };
        this.#fetch(urlObj, options, callback, error);
    };


    getDatabase(success, error) {
        error = error ?? function () {
        };
        this.#get("/endpoints/getDb.php", {}, success, error);
    }

    getCache(success, error) {
        error = error ?? function () {
        };
        this.#get("/endpoints/getCache.php", {}, success, error);
    }

    getOriginalDatabase(success, error) {
        error = error ?? function () {
        };
        this.#get("/endpoints/getOriginalDb.php", {}, success, error);
    }


    loadNode(nodeId, success, error) {
        this.#post("/endpoints/loadNode.php", {id: nodeId}, success, error);
    }

    clearCache(success, error) {
        this.#post("/endpoints/clearCache.php", {}, success, error);
    }

    renameNode(nodeId, name, success, error) {
        this.#post("/endpoints/renameNode.php", {id: nodeId, name: name}, success, error);
    }
    createNode(parentId, name, success, error) {
        this.#post("/endpoints/createNode.php", {parentId: parentId, name: name}, success, error);
    }

    deleteNode(nodeId, success, error) {
        this.#post("/endpoints/deleteNode.php", {id: nodeId}, success, error);
    }

    save( success, error) {
        this.#post("/endpoints/save.php", {}, success, error);
    }
}
