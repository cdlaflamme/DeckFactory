
document.addEventListener('DOMContentLoaded', function(){
    console.log("Hello world!");
});

// $(document).ready(function() {
//     console.log("Starting function...");
//     $.ajax({
//         url: "{{ path('ajax.deck.job_status', {deckUid: deck.uid}) }}",
//         type: 'POST',
//         dataType: 'json',
//         async: true,
//
//         success: function (data, status) {
//             console.log("Successful AJAX request...");
//             let jobStatus = data['jobStatus'];
//             let jobStatusName = 'Unknown (an error occurred)';
//             let downloadLink = '';
//             switch (jobStatus) {
//                 case 0: // Not started
//                     jobStatusName = 'Not started';
//                     break;
//                 case 1: // Started
//                     jobStatusName = 'Started';
//                     break;
//                 case 2: // Complete
//                     jobStatusName = 'Complete';
//                     downloadLink = "{{ deck.getLocalFilename() }}";
//             var $downloadField = $('#download-field');
//             $downloadField.html('<a href="#">Download Deck</a>');
//             $downloadField.attr('href', downloadLink);
//             break;
//         }
//             $('#job-status').html(jobStatusName);
//         },
//         error: function (xhr, textStatus, errorThrown) {
//             alert('Ajax request failed.');
//         }
//     });
// });
