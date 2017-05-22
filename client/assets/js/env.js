(function (window) {
    console.log("windows charging")
    window.__env = window.__env || {};

    // API url
    window.__env.apiUrl = 'http://localhost:8888/connectmangas/server/';

    // Whether or not to enable debug mode
    // Setting this to false will disable console output
    window.__env.enableDebug = true;
}(this));