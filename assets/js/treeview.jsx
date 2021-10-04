function TreeView(props) {
    let nodes = props.data;
    let [selected, setSelected] = React.useState(props.selected);
    selected = props.selected ?? selected;
    let onSelect = props.onSelect ?? function(){};
    let [selectedControls,setSelectedControls] = React.useState(props.selectedControls);
    selectedControls = props.selectedControls ?? selectedControls;

    if (nodes.length === 0) {
        return (<div><i>Empty</i></div>);
    }
    function select(node)
    {
        if(selected && selected.id === node.id)
        {
            console.log("Wiping selection");
            select({id: null});
            return;
        }
        if(node.deleted)
        {
            return;
        }

        let result = onSelect(node);
        if(!result)
        {
            return;
        }
        setSelectedControls(result);
        setSelected(node);
    }
    return (
        <div className={"element"}>
            {nodes.map(node =>
                <div key={node.id} className={props.showRoots ? "root" : ""}>
                    <div onClick={() => select(node)} className={"node" + ((selected && selected.id === node.id) ? " selected" : "") + (node.deleted ? " deleted" : "")}>
                        <div className={"title"}>{node.value}{node.deleted ? " (deleted)" : ""}</div>
                        {(selected && selected.id === node.id) && <div className={"controls"}>{selectedControls}</div>}
                    </div>

                    {node.children.length > 0 && (
                        <div className={"children"}>
                            <TreeView data={node.children} selected={selected} onSelect={select} selectedControls={selectedControls}/>
                        </div>
                    )}
                </div>
            )}
        </div>
    );

}
