let intervalId = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log("Hello world!");

    getDeckStatus();
    intervalId = setInterval(getDeckStatus, 5000);
});

// https://code.tutsplus.com/articles/create-a-javascript-ajax-post-request-with-and-without-jquery--cms-39195
function getDeckStatus(){
    // let downloadField = document.querySelector('.download-field');
    let downloadField = document.getElementById('download-field');
    console.log("Starting interval...");
    fetch(downloadField.dataset.ajaxPath, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then( response => {
        return response.json();
    }).then( response => {
        // let data = response.json();
        console.log("Received AJAX response...");
        let jobStatus = response['jobStatus'];
        let jobStatusName = 'Unknown (an error occurred)';
        switch (jobStatus) {
            case 0: // Not started
                jobStatusName = 'Not started';
                break;
            case 1: // Started
                jobStatusName = 'Started';
                break;
            case 3: // Successful
                jobStatusName = 'Complete';
                let filename = downloadField.dataset.filename;
                // Template string for sprintf-like functionality; requires JS 6+
                downloadField.innerHTML = `<a id="download-link" href="download" download="${filename}" target="_blank">Download Deck</a>`;
                document.getElementById('download-link').click(); // Auto-start the download
                clearInterval(intervalId);
                break;
        }
        document.getElementById('job-status').innerText = jobStatusName;
        console.log("Processed AJAX response.");
    }).catch(error => {
        console.log('Ajax request experienced an error.');
    });
}


