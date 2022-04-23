jQuery(document).ready(function($) {
    $( ".closebtn" ).click(function() {
      $('#block-topheaderblock').css('display','none');
    });

    $( ".close-modal" ).click(function() {
      $('#myModal').css('display','none');

      $.ajax({
        url: Drupal.url('parfum/checkcookies'),
        type: 'POST',
        dataType: 'json',
      });    
    });
})

// Get the modal
var modal = document.getElementById('myModal');

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close-modal")[0];

// When the user clicks on <span> (x), close the modal


// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}