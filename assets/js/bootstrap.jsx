'use strict';

console.log("Starting The App");
const domContainer = document.querySelector('#app');
let api = new Api();
ReactDOM.render(React.createElement(App, {api}), domContainer);

