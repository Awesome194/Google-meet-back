<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" href="/assets/images/favicon.png" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Honey Comics</title>
<!-- adding bootstrap.css and the needed styling -->
<link href="{{ asset('chargebee') }}/assets/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('chargebee') }}/assets/css/style.css" rel="stylesheet">
<!-- adding HTML5.js -->
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function() {
   queryParameters = window.location.search.substring(1);
   strings = queryParameters.split("&")
   $('.first-name').text(strings[0].substring(strings[0].indexOf("=")+1));
   $('.plan-id').text(strings[1].substring(strings[1].indexOf("=")+1));
});    
</script>
</head>
<body>
<div class="navbar navbar-static-top">
<div class="container">
{{-- <div class="navbar-header">          
<div class="h1"></div>
</div> --}}
</div>
</div>
<div class="jumbotron text-center">
    <div class="container">
    	<h2><span class="text-muted">Congrats! You've</span> successfully subscribed <span class="text-muted">to Honey Comics.</span></h2>
        <h4 class="text-muted">Comics will be delivered to your doorstep starting next month.</h4>
        <h1>Thank You!</h1>
    </div>
</div>
<footer class="footer text-center">
<span class="text-muted">&copy; Honey Comics. All Rights Reserved.</span>
</footer> 
</body>
</html>