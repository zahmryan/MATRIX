jQuery(document).ready(function($)
{
	//$("#reply_form").hide();
	$("#fullrecord_display").hide();
	
	var amplificationid = $("#amplificationid").val();
	
	//When a certain amplification is clicked
	$(function(){
		$.ajax({
			type: "GET",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/fullrecordnew.php",
			data: {'amp_id' : amplificationid},
			dataType: "json",
			success: function(data) {
				$("#fullrecord_display").show();
				//Display the generic information
				if($.isEmptyObject(data.url) == false)
				{
					$("#fr_title").text(data.title);
					var a = document.getElementById('fr_titlelink');
					a.setAttribute("href", data.url);
					$("#fr_download").hide();
				}
				else
				{
					$("#fr_title").text(data.title);
				}
				$("#fr_author").append("by " + data.author);
			//	$("#fr_author").append("by " + data.author+"<br />"+data.timestamp);
				$("#fr_timestamp").append("" + data.timestamp);
				if($.isEmptyObject(data.url) == false)
				{
					$("#fr_url").append("<strong>URL:</strong> ");
					$("#fr_url").append("<a href='" + data.url + "'>" + data.url + "</a>");
				}
				else
				{
					if(($.isEmptyObject(data.text_sub) == true) && ($.isEmptyObject(data.media) == false)){
						$("#fr_media").append("<strong>Media:</strong> " + data.media);
					}
				}
				if($.isEmptyObject(data.desc) == false)
				{		
					$("#fr_desc").append("<strong>Description:</strong> " + data.desc);
				}

				if($.isEmptyObject(data.keyword) == false)
				{
					$("#fr_keyword").append("<strong>Keywords:</strong> ");
					for(i = 0; i < data.keyword.length; i++)
					{
						if(i != (data.keyword.length-1))
							$("#fr_keyword").append("<a href='" + BASE_URL + "/current/?keyword=" + data.keyword[i] + "'>" + data.keyword[i] + "</a> <span class='deprecated'>" + data.deprecated[i] + "</span> <b>|</b> " );
						else
							$("#fr_keyword").append("<a href='" + BASE_URL + "/current/?keyword=" + data.keyword[i] + "'>" + data.keyword[i] + "</a> <span class='deprecated'>" + data.deprecated[i] + "</span> " );
					}
				}
				if($.isEmptyObject(data.rating) == false)
				{
					$("#fr_rating").append("<strong>Rating:</strong> " + data.rating);
				}

				if($.isEmptyObject(data.citation) == false)
				{
					$("#fr_citation").append("<strong>Citation:</strong> " + data.citation);
				}
				
				if($.isEmptyObject(data.copyright) == false)
				{
					$("#fr_copyright").append("<strong>Copyright:</strong> " + data.copyright);
				}
				$("#fr_user").append("<strong>User:</strong> ");
				$("#fr_user").append("<a href='" + BASE_URL + "/profile/?user=" + data.username + "'>" + data.user + "</a>");
				
				if($.isEmptyObject(data.review) == false)
				{
					$("#fr_review").append("<strong>Review:</strong> " + data.review);
				}	
				if($.isEmptyObject(data.tags) == false){
					var tags_array = data.tags.split(" ");
					var num_tags = tags_array.length;
					$("#fr_tags").append("<strong>Tags:</strong> ");
					for (var i = 0; i < num_tags; i++) {
						var tag = "<a href='" + BASE_URL + "/current/?tags=" + tags_array[i] + "'>" + tags_array[i] + "</a>";
						$("#fr_tags").append(tag + " ");
					}
				}
			},
			error: function(data){
				console.log(data);
				alert("Server Error");
			}
		});

		load_comments(amplificationid);
	});
	
	//when submitting new comment form
	$("#submit_comment:button").click(function() {
		var comment = $("textarea[name=comment_box]").val();
		var anon = $("input[name=comment_anon]:checked").val();

		//ajax call to add comment to database
		$.ajax({
			type:"POST",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/add_comment.php",
			data: {'comment' : comment, 'amp_id' : amplificationid, 'anon' : anon},
			dataType: "json",
			success: function(data)
			{
				load_comments(amplificationid);
			},

			error: function(data){
				alert("Submit Comment Error");
			}
		});
	});

	//clicking on users in permissions
	$("#permission_users").change(function() {
		var user = $("#permission_users option:selected").text();
		$("#Perm_selected_user").text(user);
		$("#perm_update").show();
	});

	//submitting permission changes
	$("#Perm_submit").click(function() {
		var permission = $("input[name=permission_type]:checked").val();
		var user = $("#permission_users").val();
		var name = $("#permission_users option:selected" ).text();
		$.ajax({
			type:"GET",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/permission_update.php",
			data: {'amp_id' : amplificationid, 'user' : user},
			dataType: "json",
			success: function(data)
			{
				alert(name + data);
			}
		});
	});

	//Pagination of text
		var textfile = $("#fr_textblock").text();
		if(textfile.length > 5000){
			var page_count = Math.ceil(textfile.length/5000);
			$.ajax({
				type: "POST",
				url: BASE_URL + "wp-content/themes/Flatter/ajax/load_page.php",
				data: {'textfile': textfile, 'page': 1},
				dataType: "html",
				success: function(data)
				{
					$("#fr_textblock").html(data);
					$("#fr_textblock").after("<div id = 'fr_text_pages'><b>Go to Page: </b>");
					for(i = 1; i <= page_count; i++)
					{
						$("#fr_text_pages").append("<input class='page_number' type='button' value='"+i+"'>");
					}
					$("#fr_text_pages").append("</div>");
					$(".page_number").click(function() {
						window.scroll(0,0);
						var page = $(this).val();
						$.ajax({
							type: "POST",
							url: BASE_URL + "wp-content/themes/Flatter/ajax/load_page.php",
							data: {'textfile': textfile, 'page': page},
							dataType: "html",
							success: function(data)
							{
								$("#fr_textblock").html(data);
							}
						});
					});
				}
			});
		}

	//comments character count
	$("textarea[name=comment_box]").bind('input propertychange', function() {
		var comment = $("textarea[name=comment_box]").val();
		var com_length = comment.length;
		if(com_length > 65535){
			$("#character_count").text("Comment must be no longer than 65535 Characters:\n " + com_length + " characters");
			$("#character_count").css( "color", "red");
			$("#submit_comment").hide();

		}
		else{
			$("#character_count").text(com_length + " characters");
			$("#character_count").css("color", "");
			$("#submit_comment").show();
		}
	});


	$("#fr_change_display").click(function() {
		function change_display(display,amplificationid){
			$.ajax({
				type:"GET",
				url: BASE_URL + "wp-content/themes/Flatter/ajax/fr_display_options.php",
				data: {'amp_id' : amplificationid, 'display': display},
				dataType: "json",
				success: function(data)
				{
					alert("success!");
				},
				error: function(data)
				{
					alert("error");
				}
			});
		}
		if($("#fr_options").val() == "fr_pending"){
			var display = "Pending";
			if(confirm("Pressing OK will Set this Record to Pending") == true) {
				change_display(display,amplificationid);
			}
		}
		else if($("#fr_options").val() == "fr_accepted"){
			var display = "Accepted";
			if(confirm("Pressing OK will Accept This Record") == true) {
				change_display(display,amplificationid);
			}
		}
		else if($("#fr_options").val() == "fr_declined"){
			var display = "Declined";
			if(confirm("Pressing OK will Decline This Record") == true) {
				change_display(display,amplificationid);
			}
		}
		else if($("#fr_options").val() == "fr_deleted"){
			var display = "Deleted";
			if(confirm("Pressing OK will Delete This Record") == true) {
				change_display(display,amplificationid);
			}
		}
	});
	
	$("#fr_invite_to_official_publication").click(function(){
		$.ajax({
			type:"GET",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/fr_invite.php",
			data: { 'amp_id' : amplificationid },
			dataType: 'json',
			success: function(data){
				$(".content").hide();
				$("#invitation_success").show();
				setTimeout('window.location.href="' + BASE_URL + 'full-record/?amplificationid=' + amplificationid + '"', 2000)
			},
			error: function(data){
				console.log(data);
				alert("Failure to send invitation");
			}
		});
	});
	
	//Full record download
	$("#fr_download").click(function(){
		window.location.assign(BASE_URL + 'wp-content/themes/Flatter/ajax/fr_download.php/?amp_id=' + amplificationid);
	});


	//Full record Edit
	$("#fr_edit_errors").click(function(){
		$.ajax({
			type: "GET",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/fullrecordnew.php",
			data: {'amp_id' : amplificationid},
			dataType: "json",
			success: function(data) {
				$("#fr_finish_edit").show();
				var title = data.title;
				var desc = data.desc;
				var citation = data.citation;
				var copyright = data.copyright;
				$("#fr_title").html("<form class='fr_edit_form' method='post' action=''><input type='text' id='fr_title_edit' value='"+title+"'><input class='fr_editform_submit' type='button' value='Submit Changes' />");
				$("#fr_desc").html("<form class='fr_edit_form' method='post' action=''><strong>Description:</strong><textarea id='fr_desc_edit'>"+desc+"</textarea><input class='fr_editform_submit' type='button' value='Submit Changes' />");
				$("#fr_citation").html("<form class='fr_edit_form' method='post' action=''><strong>Citation:</strong><input type='text' id='fr_citation_edit' value='"+citation+"'><input class='fr_editform_submit' type='button' value='Submit Changes' />");
				$("#fr_copyright").html("<form class='fr_edit_form' method='post' action=''><strong>Copyright:</strong><input type='text' id='fr_copyright_edit' value='"+copyright+"'><input class='fr_editform_submit' type='button' value='Submit Changes' />");
				$(".fr_editform_submit").click(function(){
					var title = $("#fr_title_edit").val();
					var desc = $("#fr_desc_edit").val();
					var citation = $("#fr_citation_edit").val();
					var copyright = $("#fr_copyright_edit").val();
					$.ajax({
						type: "GET",
						url: BASE_URL + "wp-content/themes/Flatter/ajax/edit_fullrecord_errors.php",
						data: {"amp_id" : amplificationid, 'title' : title, 'desc' : desc, 'citation' : citation, 'copyright' : copyright},
						dataType: "json",
						success: function(data) {
							alert("Edit complete, you can continue to edit or click Finish Editing");
						}
					});
				});
				$("#fr_finish_edit").click(function(){
					$.ajax({
						type: "GET",
						url: BASE_URL + "wp-content/themes/Flatter/ajax/fullrecordnew.php",
						data: {'amp_id' : amplificationid},
						dataType: "json",
						success: function(data) {
							$("#fr_finish_edit").hide();
							var title = data.title;
							var desc = data.desc;
							var citation = data.citation;
							var copyright = data.copyright;
							$("#fr_title").html(title);

							if($.isEmptyObject(data.desc) == false)
							{		
								$("#fr_desc").html("<strong>Description:</strong> " + data.desc);
							}

							if($.isEmptyObject(data.copyright) == false)
							{
								$("#fr_copyright").html("<strong>Copyright:</strong> " + data.copyright);
							}

							if($.isEmptyObject(data.citation) == false)
							{
								$("#fr_citation").html("<strong>Citation:</strong> " + data.citation);
							}
						}
					});
				});
			}
		});
	});
	$.ajax({
		type:"GET",
		url: BASE_URL + "wp-content/themes/Flatter/ajax/publishrecord.php",
		data: {'amp_id' : amplificationid, 'pubcheck' : true},
		dataType: "json",
		success: function(data)
		{
			if(data == true){
				$("#fr_published").replaceWith("<strong>Published</strong> Record");
			}
		}
	});

	$("#fr_published").click(function(){
		$.ajax({
			type:"GET",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/publishrecord.php",
			data: {'amp_id' : amplificationid},
			dataType: "json",
			success: function(data)
			{
				alert("Record has been Published!");
			}
		});
	});
	$('video,audio').mediaelementplayer(/* Options */);
});

function load_comments(amp_id){
	jQuery(document).ready(function($)
	{
		$.ajax({
			type:"GET",
			url: BASE_URL + "wp-content/themes/Flatter/ajax/load_comments.php",
			data: {'amp_id' : amp_id},
			dataType: "html",
			success: function(data)
			{
				$("#comments").html(data);
				$(".comment_id").each(function() {
					if(typeof $(this).parent().find(".parent_id").val() != "undefined")
					{
						//replys are here. must take parent id and display underneath
						var reply = $(this).parent();
						var parent_id = $(this).parent().find(".parent_id").val();
						$(".comment_id").each(function() {
							if($(this).val() == parent_id){
								//this is the parent of the reply
								var parent = $(this).parent();
								//WIP
								$(reply).insertAfter(parent);
								margin = parent.css("margin-left");

								//indent display for replys, Currently allows 6 levels of replys
								if(margin == "50px"){
									$(reply).css("margin-left", "100px");
								}
								else if(margin == "100px"){
									$(reply).css("margin-left", "150px");
								}
								else if(margin == "150px"){
									$(reply).css("margin-left", "200px");
								}
								else if(margin == "200px"){
									$(reply).css("margin-left", "250px");
								}
								else if(margin == "250px"){
									$(reply).css("margin-left", "300px");
									reply.find("#comment_reply").hide();
								}
								else{
									$(reply).css("margin-left", "50px");
								}
							}
						});
					}
				});
			}
		});
	});
}

function reply_click(click) {
	jQuery(document).ready(function($)
	{
		var amplificationid = $("#amplificationid").val();
		var parent = $(click).parent();
		$("#reply_form").insertAfter(parent);
		$("#reply_form").show();

		$("#cancel_reply:button").click(function() {
			$("#reply_form").hide();
		});

		$("#submit_reply:button").click(function() {
			var reply = $("textarea[name=reply_box]").val();
			var id_form = $(".comment_id");
			var id = $(click).parent().find(id_form).val();
			var anon = $("input[name=reply_anon]:checked").val();

			//Adding the reply to the comment database
			$.ajax({
				type:"POST",
				url: BASE_URL + "wp-content/themes/Flatter/ajax/add_reply.php",
				data: {'reply' : reply, 'amp_id' : amplificationid, 'id': id, 'anon': anon},
				dataType: "json",
				success: function(data)
				{
					load_comments(amplificationid);
				},

				error: function(data)
				{
					alert("Reply Error");
				}
			});
		});
	});
}


function delete_click(click) {
	jQuery(document).ready(function($)
	{
		var amplificationid = $("#amplificationid").val();
		var id_form = $(".comment_id");
		var id = $(click).parent().find(id_form).val();
		
		if(confirm("Are you sure you want to delete this comment?") == true) {
			$.ajax({
				type:"GET",
				url: BASE_URL + "wp-content/themes/Flatter/ajax/delete_comment.php",
				data: {'id' : id},
				dataType: "json",
				success: function(data)
				{
					$(".comment_id").each(function() {
						if(typeof $(this).parent().find(".parent_id").val() != "undefined")
						{
							if(id == ($(this).parent().find(".parent_id").val()))
							{	
								$.ajax({
									type:"GET",
									url: BASE_URL + "wp-content/themes/Flatter/ajax/delete_comment.php",
									data: {'id' : id},
									dataType: "json"
								});
							}
						}
					});
					load_comments(amplificationid);
				},

				error: function(data)
				{
					alert("delete ajax error");
				}
			});
		}
	});
}

function edit_click(click) {
	jQuery(document).ready(function($)
	{
		var amplificationid = $("#amplificationid").val();
		var id_form = $(".comment_id");
		var id = $(click).parent().find(id_form).val();
		var comment = $(click).parent().find("#comment").text();

		$(click).parent().find("#comment").html("<form id='new_edit' method='post' action=''><textarea name = 'edit_box' cols='25' rows='5'>"+comment+"</textarea><input id='submit_edit' type='button'  value='Submit' /><input id='cancel_edit' type='button' value='Cancel' /></form>");

		$("#cancel_edit:button").click(function() {
			$("#new_edit").hide();
			$(click).parent().find("#comment").html(comment);
		})

		$("#submit_edit:button").click(function() {
			var edit = $("textarea[name=edit_box]").val();

			$.ajax({
				type: "GET",
				url: BASE_URL + "wp-content/themes/Flatter/ajax/edit_comment.php",
				data: {'id' : id, 'edit' : edit},
				dataType: "json",
				success: function(data)
				{
					$(click).parent().find("#comment").html(edit);
				}
			});
		});
	});
}