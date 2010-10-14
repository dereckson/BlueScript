<html>
<head>
<title>@blue</title>
<script type="text/javascript" src="include/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="include/js/jquery-ui-1.8.custom.min.js"></script>
<link rel='stylesheet' href='layouts/blue/css/default/jquery-ui-1.8.custom.css' type='text/css' />
<link rel='stylesheet' href='layouts/blue/css/style.css' type='text/css' />
</head>
<body>
	<script type="text/javascript">
	$(document).ready(function() {
		$("li.folder").prepend("+ ");
		$("li.folder").next().hide();
		$("li.folder").css("cursor", "pointer");

		$("li.folder").click(function() {
			var options = {};
			$(this).next().slideToggle('fast');

			var v = $(this).html().substring( 0, 1 );
			if ( v == "+" )
				$(this).html( "-" + $(this).html().substring( 1 ) );
			else if ( v == "-" )
				$(this).html( "+" + $(this).html().substring( 1 ) );
		});

		$("#imagedialog").dialog({
			autoOpen: false,
			height: 768,
			width: 1024,
			modal: true
		});

		$('.image').click(function() {
			var url = $(this).attr('href');
			var img = new Image();
			img.src = url;

			$("#imagedialog").attr('title', 'Image');
			$("#imagedialog").html('<img width="990" src="'+url+'" />');
			$("#imagedialog").dialog('open');
			return false;
		});
	});
	</script>

	<div id="imagedialog" title="Image">

	</div>

	<div id="container-o">
    	<div id="container-i">
        	<div class="right" id="content">
        		{if $include}
			    <div id="box">
			    	{$include}
			    </div>
			    {/if}
        		<div id="box">
					<h3>Files:</h3>
					{$directory}
				</div>
        	</div>

         	<div class="left" id="side">
				<div id="box">
					<img src="images/avatar.jpg" width="168" />
					<hr />
					<b>Mail:</b><br />
					wb10.80@gmail.com<br />
					blue@devio.us<br />
					<b>Jabber:</b><br />
					blue-dev@swissjabber.org
					<hr />
					<a href="code/php/homepage/latest.tar.gz">Sourcecode</a> (Public Domain)
					<hr />
					All content (c) by me
				</div>
			</div>

	        <div class="clear">
    	        &nbsp;
        	</div>
      	</div>
	</div>

	<div id="footer">
		(c) 2009-2010 <b>wb10.80@gmail.com</b> // <b>blue@devio.us</b>
	</div>

</body>
</html>
