<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="icon" type="image/png" href="assets/images/favicon.png" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- adding bootstrap.css and the needed styling -->
        <title>Honey Comics</title>
        <link href="{{ asset('chargebee') }}/assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('chargebee') }}/assets/css/style.css" rel="stylesheet">
        <!-- Adding HTML5.js -->
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
        
        
        <script type="text/javascript" src="https://js.stripe.com/v2/">
        </script>
        
        
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.js"></script>
        <script type="text/javascript" src="https://malsup.github.io/jquery.form.js"></script>
        <script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.js"></script>
        
        
        <!-- Setting the stripe publishable key.-->
        <script>Stripe.setPublishableKey("{{ Config::get('chargebee.stripe_public_key') }}");
        </script>
        
        
        <!-- It is better to have the below script as separate file.-->
        <script type="text/javascript">
            // Setting the error class and error element for form validation.
            jQuery.validator.setDefaults({
                errorClass: "text-danger",
                errorElement: "small"
            });

            
            // Call back function for stripe response.
            function stripeResponseHandler(status, response) {
                if (response.error) {
                    // Re-enable the submit button
                    $('.submit-button').removeAttr("disabled");
                    // Show the errors on the form
                    stripeErrorDisplayHandler(response);
                    $('.subscribe_process').hide();
                } else {
                    var form = $("#subscribe-form");
                    // Getting token from the response json.
                    var token = response['id'];
                    // insert the token into the form so it gets submitted to the server
                    if ($("input[name='stripeToken']").length == 1) {
                        $("input[name='stripeToken']").val(token);
                    } else {
                        form.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
                    }
                    var options = {
                        // post-submit callback when error returns
                        error: subscribeErrorHandler, 
                        // post-submit callback when success returns
                        success: subscribeResponseHandler, 
                        complete: function() {
                            $('.subscribe_process').hide()
                        },
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        dataType: 'json'
                    };
                    // Doing AJAX form submit to your server.
                    form.ajaxSubmit(options);
                    return false;
                }
            }
            

            // Handling and displaying error during form submit.
            function subscribeErrorHandler(jqXHR, textStatus, errorThrown) {
                    try{
                        var resp = JSON.parse(jqXHR.responseText);
                        if ('error_param' in resp) {
                            var errorMap = {};
                            var errParam = resp.error_param;
                            var errMsg = resp.error_msg;
                            errorMap[errParam] = errMsg;
                            $("#subscribe-form").validate().showErrors(errorMap);
                        } else {
                            var errMsg = resp.error_msg;
                            $(".alert-danger").show().text(errMsg);
                        }
                    } catch(err) {
                        $(".alert-danger").show().text("Error while processing your request");
                    }
            }
            
            // Forward to thank you page after receiving success response.
            function subscribeResponseHandler(responseJSON) {
                window.location.replace(responseJSON.forward);
            }

            
            // Handling the error from stripe server due to invalid credit card credentials.
            function stripeErrorDisplayHandler(response) {
                //Card field map - the keys are taken from error param values sent from stripe 
                //                 and the values are error class name in the form.
                var errorMap = {"number": "card-number",
                    "cvc": "card-cvc",
                    "exp_month": "card-expiry-month",
                    "exp_year": "card-expiry-year"
                };
                //Check if param exist in error
                if (response.error.param) {
                    var paramClassName = errorMap[response.error.param];
                    if (paramClassName) {
                        //Display error in found class
                        $('.' + paramClassName)
                                .parents('.form-group')
                                .find('.text-danger')
                                .text(response.error.message).show();
                    } else {
                        $(".alert-danger").show().text(response.error.message);
                    }
                } else {
                    $(".alert-danger").show().text(response.error.message);
                }
            }
            
            
            $(document).ready(function() {
                $("#subscribe-form").validate({
                    rules: {
                        zip_code: {number: true},
                        phone: {number: true}
                    }
                });

                function formValidationCheck(form) {
                    // Checking form has passed the validation.
                    if (!$(form).valid()) {
                        return false;
                    }
                    $(".alert-danger").hide();
                    $('.subscribe_process').show();
                }
                
                $("#subscribe-form").on('submit', function(e) {
                    // form validation
                    formValidationCheck(this);
                    if(!$(this).valid()){
                        return false;
                    }
                    // Disable the submit button to prevent repeated clicks and form submit
                    $('.submit-button').attr("disabled", "disabled");
                    // createToken returns immediately - the supplied callback 
                    // submits the form if there are no errors
                    Stripe.createToken({
                        number: $('.card-number').val(),
                        cvc: $('.card-cvc').val(),
                        exp_month: $('.card-expiry-month').val(),
                        exp_year: $('.card-expiry-year').val()
                    }, stripeResponseHandler);
                    return false; // submit from callback
                });
                
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
        <div id="container" class="checkout container">                        

            <div class="row">		
                <div class="col-sm-4 pull-right hidden-xs" id="order_summary">
                    <br><br><br><br>                
                   {{--  <img src="assets/images/secure.png" alt="secure server"/> --}}
                    <br><br>
               {{--      <div class="using">                    
                        <img src="assets/images/guarantee.jpg">
                        <br>
                        <hr class="dashed">
                        <h5>Powered by</h5>                    
                        <img src="assets/images/powered.png">
                    </div> --}}
                </div>
                <div class="col-sm-7" id="checkout_info">   
                    <!-- Add the needed fields in the form-->    
                    
                    <form action="{{ url('payment') }}" method="post" id="subscribe-form">
                      @csrf
                      <input type="hidden" name="subscription_id" value="{{ $id  ?? '' }}">
						<h3 class="page-header">Tell us about yourself</h3>
                        <div class="row">
                          @php
                                $name = !empty($user->name) ? explode(' ',$user->name) : [];
                                $firstName = !empty($name[0]) ? $name[0] : '' ;
                                $lastName = !empty($name[1]) ? $name[1] : '' ;
                          @endphp
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="customer[first_name]">First Name</label>
                                    <input type="text" class="form-control" name="customer[first_name]" 
                                          maxlength=50 required data-msg-required="cannot be blank" value="{{ $firstName  ?? '' }}">
                                    <small for="customer[first_name]" class="text-danger"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="customer[last_name]">Last Name</label>
                                    <input type="text" class="form-control" name="customer[last_name]" 
                                           maxlength=50 required data-msg-required="cannot be blank" value="{{ $lastName ?? '' }}">
                                    <small for="customer[last_name]" class="text-danger"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                                                        
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="customer[email]">Email</label>
                                    <input id="email" type="text" class="form-control" name="customer[email]" maxlength=50
                                               data-rule-required="true" data-rule-email="true" 
                                               data-msg-required="Please enter your email address" 
                                               data-msg-email="Please enter a valid email address" value="{{ $user->email ?? '' }}">
                                    <small for="customer[email]" class="text-danger"></small>
                                </div>
                            </div> 
                                                        
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="customer[phone]">Phone</label>
                                    <input id="phone" type="text" maxlength="10" class="form-control" name="customer[phone]" 
                                           maxlength=20 required data-msg-required="cannot be blank">
                                    <small for="customer[phone]" class="text-danger"></small>
                                </div>
                            </div>                   
                        </div>     
                        <h3 class="page-header">Where would you like us to deliver</h3>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="addr">Address</label>
                                    <input type="text" class="form-control" name="addr" 
                                           maxlength=50 required data-msg-required="cannot be blank">
                                    <small for="addr" class="text-danger"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="extended_addr">Address2</label>
                                    <input type="text" class="form-control" name="extended_addr" maxlength=50>
                                    <small for="extended_addr" class="text-danger"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" class="form-control" name="city" maxlength=50
                                           required data-msg-required="cannot be blank">
                                    <small for="city" class="text-danger"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" class="form-control" name="state" maxlength=20
                                           required data-msg-required="cannot be blank">
                                    <small for="state" class="text-danger"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="zip_code">Zip Code</label>
                                    <input id="zip_code" type="text" class="form-control" name="zip_code" 
                                           maxlength=10 required number data-msg-required="cannot be blank">
                                    <small for="zip_code" class="text-danger"></small>
                                </div>
                            </div>                                                
                        </div>
                        <h3 class="page-header">Payment Information</h3>
                        <div class="row">                 	  
                            <div class="col-sm-12">
                                
                                <div class="form-group">
                                    <label for="card_no">Credit Card Number</label>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <input type="text" class="card-number form-control" id="card_no" 
                                                   required data-msg-required="cannot be blank" value=""> 
                                        </div>
                                        <div class="col-sm-6">                      	
                                            <span class="cb-cards hidden-xs">                                        
                                                <span class="visa"></span>                                        
                                                <span class="mastercard"></span>
                                                <span class="american_express"></span>
                                                <span class="discover"></span>
                                            </span> 
                                        </div>
                                    </div>
                                    <small for="card_no" class="text-danger"></small>
                                </div>
                                
                            </div>                                                             
                        </div>
                        <div class="row">                
                            <div class="col-sm-6">                                	
                                <div class="form-group">
                                    <label for="expiry_month">Card Expiry</label>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <select class="card-expiry-month form-control" id="expiry_month" 
                                                    required data-msg-required="empty">
                                                <option selected>01</option>
                                                <option>02</option>
                                                <option>03</option>
                                                <option>04</option>
                                                <option>05</option>
                                                <option>06</option>
                                                <option>07</option>
                                                <option>08</option>
                                                <option>09</option>
                                                <option>10</option>
                                                <option>11</option>
                                                <option>12</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-6">
                                            <select class="card-expiry-year form-control" id="expiry_year" 
                                                    required data-msg-required="empty">
 						<option>2018</option>
                                                <option>2019</option>
                                                <option>2020</option>
                                                <option>2021</option>
                                                <option>2022</option>
                                                <option>2023</option>
                                                <option>2024</option>
                                                <option>2025</option>
                                                <option>2026</option>
                                                                                           </select>
                                        </div>
                                    </div> 
                                    <small for="expiry_month" class="text-danger"></small>
                                </div>                                       
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="ccv">CVC</label>
                                    <div class="row">                                    	
                                        <div class="col-xs-6">                                            
                                            <input type="text" class="card-cvc form-control" id="cvc" placeholder="CVC" 
                                                   required data-msg-required="empty" value="">
                                        </div>
                                        <div class="col-xs-6">                                            	
                                            <h6 class="cb-cvv"><small>(Last 3-4 digits)</small></h6>
                                        </div>
                                    </div>
                                    <small for="cvc" class="text-danger"></small>
                                </div>
                            </div>                                      
                        </div>
                        <hr>                            
                        <p>By clicking Subscribe, you agree to our privacy policy and terms of service.</p>
                        <p><small class="text-danger" style="display:none;">There were errors while submitting</small></p>
                         
                        <p>                          
			   <span class="subscribe_process process" style="display:none;">Processing&hellip;</span>
                            <small class="alert-danger text-danger"></small>
                        </p>
                       <div>
                           <input type="submit" class="btn btn-success btn-lg pull-left" value="Subscribe">&nbsp;&nbsp;&nbsp;&nbsp
                            <a style="margin-left: 10px;" href="{{ url('subscription') }}" class="btn btn-danger btn-lg pull-left ml-2">Cancel</a>
                      </div>
                    </form>
                    
                </div>
            </div>
        </div>
        <br><br>
        <div class="footer text-center">
            <span class="text-muted">&copy; Honey Comics. All Rights Reserved.</span>
        </div>
    </body>
</html>
