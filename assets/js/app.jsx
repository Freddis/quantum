'use strict';

function App(props) {
    let api = props.api;
    let [initialized, setInitialized] = React.useState(false);
    let [cache, setCache] = React.useState(undefined);
    let [database, setDatabase] = React.useState(null);
    let [originalDatabase, setOriginalDatabase] = React.useState(null);
    if (!database || cache === undefined || !originalDatabase) {
        initialize();
        return (<div>Loading...</div>);
    }

    return (
        <div>
            <div className={"tree"}>
                <div className="title">
                    <b>Cache</b>
                </div>
                <div className={"list"}>
                    <TreeView data={cache} showRoots={true} onSelect={selectedCacheNode}/>
                </div>
                <div className={"buttons"}>
                    <button onClick={saveCache}>Save</button>
                    <button onClick={clearCache}>Clear</button>
                    <button onClick={refreshCache}>Refresh</button>
                </div>
            </div>
            <div className={"tree"}>
                <div className="title">
                    <b>Current Database</b>
                </div>
                <div className={"list"}>
                    <TreeView data={database} onSelect={selectedDbNode}/>
                </div>
                <div className={"buttons"}>
                    <button onClick={refreshDatabase}>Refresh</button>
                </div>
            </div>
            <div className={"tree"}>
                <div className="title">
                    <b>Original Database (Immutable)</b>
                </div>
                <div className={"list"}>
                    <TreeView data={originalDatabase}/>
                </div>
                <div className={"buttons"}>
                    <form method={"POST"}>
                        <input type={"hidden"} name={"wipe"} value={"1"}/>
                        <button type={"submit"}>Wipe Cache and Reset Database</button>
                    </form>
                </div>
            </div>
        </div>
    );

    function selectedCacheNode(node) {

        return [
            <button key={1} onClick={() => createNode(node)}>Add</button>,
            <button key={2} onClick={() => renameNode(node)}>Rename</button>,
            <button key={3} onClick={() => deleteNode(node)}>Delete</button>
        ];
    }

    function refreshCache()
    {
        api.getCache(setCache, displayError);
    }

    function refreshDatabase()
    {
        api.getDatabase(setDatabase, displayError);
    }

    function createNode(node) {
        let name = prompt("Enter name:","");
        if(name == null)
        {
            return;
        }

        name = name.trim();
        if(name == "")
        {
            displayError("Don't use empty names please");
            return;
        }
        api.createNode(node.id,name,() => api.getCache(setCache,displayError),displayError);
    }
    function deleteNode(node) {
        api.deleteNode(node.id,() => api.getCache(setCache,displayError),displayError);
    }

    function saveCache() {
        api.save(() => api.getCache((result) => {setCache(result); api.getDatabase(setDatabase,displayError)}, displayError), displayError);
    }

    function renameNode(node) {
        let newName = prompt("Enter new name:",node.value);
        if(newName == null)
        {
            return;
        }
        api.renameNode(node.id,newName,() => api.getCache(setCache,displayError),displayError);
    }

    function selectedDbNode(node) {

        return [
            <button key={1} onClick={() => loadNode(node)}>Load</button>
        ];
    }

    function loadNode(node) {
        api.loadNode(node.id, () => api.getCache(setCache, displayError), displayError);
    }

    function clearCache() {
        api.clearCache(() => api.getCache(setCache, displayError), displayError);
    }

    function initialize() {
        if (initialized) {
            return;
        }
        setInitialized(true);
        console.log("Initializing");
        api.getDatabase(setDatabase, displayError);
        api.getOriginalDatabase(setOriginalDatabase, displayError);
        api.getCache(setCache, displayError);
    }

    function displayError(error) {
        alert(error);
    }
}

