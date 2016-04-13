<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<meta charset="utf-8" />
<title>TSR Radio | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta content="" name="description" />
<meta content="" name="author" />

<!-- BEGIN CORE CSS FRAMEWORK -->
<link href="/assets/plugins/boostrapv3/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="/assets/plugins/boostrapv3/css/bootstrap-theme.min.css" rel="stylesheet" type="text/css"/>
<link href="/assets/plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
<!-- END CORE CSS FRAMEWORK -->

<!-- BEGIN CSS TEMPLATE -->
<link href="/assets/css/style.css" rel="stylesheet" type="text/css"/>
<link href="/assets/css/responsive.css" rel="stylesheet" type="text/css"/>
<link href="/assets/css/custom-icon-set.css" rel="stylesheet" type="text/css"/>
<link href="/assets/css/magic_space.css" rel="stylesheet" type="text/css"/>
<!-- END CSS TEMPLATE -->

<style>
.page-content.condensed {
    margin-left: 0px;
}
.page-content .content {
    padding-top: 10px;
}
</style>

</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="">

<!-- BEGIN CONTAINER -->
<div class="page-container row-fluid">

  <!-- BEGIN PAGE CONTAINER-->
  <div class="page-content condensed">
    <div class="clearfix"></div>
    <div class="content sm-gutter">
      <div class="page-title">
        <h1>TSR Radio | Admin</h1>
      </div>
     
      <div class="row" >
       <div class="col-md-12 col-vlg-4 col-sm-12">
          <div class="row">

            <div class="col-md-8 col-vlg-4 col-sm-12 ">  
                <div class="grid simple ">                    
                    <div class="grid-body no-border">
                        <h3>Controls</h3>

						<div class="alert alert-success" style="display: none;"></div>

                        <br />
                        <button type="button" data-action="start" class="btn btn-block btn-primary btn-lg btn-large action-btn">Start</button>
                        <hr>
                        	<center>
                        		<p>Override Controls</p>
                            	<button type="button" data-action="play" class="btn btn-success btn-lg btn-large action-btn">Play</button>
                            	<button type="button" data-action="pause" class="btn btn-warning btn-lg btn-large action-btn">Pause</button>
                            	<button type="button" data-action="stop" class="btn btn-danger btn-lg btn-large action-btn">Stop</button>
                            </center>
                        <hr>
                        <h4>Actions</h4>
                        <br />
                        <button type="button" data-action="regenerate" class="btn btn-primary btn-lg btn-large action-btn">Regenerate Playlist</button>
                        </div>
                    </div>
            </div>
         </div>
       </div>
</div>

 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>

$('.action-btn').click(function(){

	action = $(this).attr('data-action');

	if(action === 'regenerate'){
    
    $('.alert').text("Playlist generation in progress...");
    $('.alert').fadeIn();

		$.get('/admin/spotify.php', function(data){
      json = jQuery.parseJSON(data);
      $('.alert').hide();
      $('.alert').text("Playlist generated - "+json.tracksAdded+" Tracks added.");
      $('.alert').fadeIn();
      setTimeout(function(){ $('.alert').fadeOut("slow") }, 3000);

    });
		
	} else {

		$.post( "action.php?action="+action, function( data ) {
			$('.alert').text(data);
  			$('.alert').fadeIn();
  			setTimeout(function(){ $('.alert').fadeOut("slow") }, 4000);
		});

	}
});

</script>

</body>
</html>