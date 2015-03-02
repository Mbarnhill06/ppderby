// Requires dashboard-ajax.js
// Requires modal.js

$(document).ajaxSuccess(function(event, xhr, options, xmldoc) {
	var passed = xmldoc.documentElement.getElementsByTagName("passed");
    if (passed && passed.length > 0) {
		var racerid = passed[0].getAttribute("racer");
		$("#passed-" + racerid).prop('checked', true);
	}
});

// This executes when a checkbox for "Passed" is clicked.
function handlechange_passed(cb, racer) {
    // cb is the checkbox element, with name "pased-" plus the racer id, e.g., passed-1234
    if (!cb.checked && !confirm("Are you sure you want to unregister " + racer + "?")) {
	    cb.checked = true;
	    return;
    }
    // 7 = length of "passed-" prefix
    var racer = cb.name.substring(7);
    var value = cb.checked ? 1 : 0;

    $.ajax(g_action_url,
           {type: 'POST',
            data: {action: 'pass',
                   racer: racer,
                   value: value},
           });
}

// This executes when a checkbox for "Exclusively by Scout" is clicked.
function handlechange_xbs(cb) {
    // cb is the checkbox element, with name "xbs-" plus the racer id, e.g., xbs-1234
    // 4 = length of "xbs-" prefix
    var racer = cb.name.substring(4);
    var value = cb.checked ? 1 : 0;

    $.ajax(g_action_url,
           {type: 'POST',
            data: {action: 'xbs',
                   racer: racer,
                   value: value},
           });
}

function show_edit_racer_form(racerid) {
  var first_name = $('#firstname-' + racerid).text();
  var last_name = $('#lastname-' + racerid).text();
  var car_no = $('#car-number-' + racerid).text();

  var rankid = $('#class-' + racerid).attr('data-rankid');

  $("#edit_racer").val(racerid);

  $("#edit_firstname").val(first_name);
  $("#edit_lastname").val(last_name);

  $("#edit_carno").val(car_no);
  $("#edit_carno").focus();

  var edit_rank = $("#edit_rank");
  edit_rank.val(rankid);
  // I think it's a bug in jquery-mobile that an explicit change
  // event is required; setting the val above should be sufficient
  // to cause an update.
  edit_rank.change();

  $("#eligible").prop("checked", $('#lastname-' + racerid).attr("data-exclude") == 0);
  $("#eligible").trigger("change", true);

  show_modal("#edit_racer_modal", function(event) {
      handle_edit_racer();
      return false;
  });
}

function show_new_racer_form() {
  $("#edit_racer").val(-1);

  $("#edit_firstname").val("");
  $("#edit_firstname").focus();

  $("#edit_lastname").val("");

  $("#edit_carno").val(9999);
  $("#eligible").val(true);
  $("#eligible").trigger("change", true);

  show_modal("#edit_racer_modal", function(event) {
      handle_edit_racer();
      return false;
  });
}

function handle_edit_racer() {
  close_modal("#edit_racer_modal");

  var racerid = $("#edit_racer").val();

  var new_firstname = $("#edit_firstname").val();
  var new_lastname = $("#edit_lastname").val();
  var new_carno = $("#edit_carno").val();
  var new_carname = $("#edit_carname").val();

  var rank_picker = $("#edit_rank");
  var new_rankid = rank_picker.val();

  var rank_option = $('[value="' + new_rankid + '"]', rank_picker);
  var new_classname = rank_option.attr('data-class');
  var new_rankname = rank_option.attr('data-rank');

  var exclude = $("#eligible").is(':checked') ? 0 : 1;

  $.ajax(g_action_url,
         {type: 'POST',
          data: {action: racerid >= 0 ? 'edit-racer' : 'new-racer',
                 racer: racerid,
                 firstname: new_firstname,
                 lastname: new_lastname,
                 carno: new_carno,
                 carname: new_carname,
                 rankid: new_rankid,
                 exclude: exclude},
            success: function(data) {
                var new_row_elements = data.getElementsByTagName('new-row');
                if (new_row_elements.length > 0) {
	                tb = $(".main_table tbody");
                    for (var j = 0; j < new_row_elements.length; ++j) {
                        var tr_elements = new_row_elements[j].getElementsByTagName('tr');
                        for (var jj = 0; jj < tr_elements.length; ++jj) {
                            var new_tr = document.createElement('tr');
	                        tb.get(0).appendChild(new_tr);
                            new_tr.outerHTML = (new XMLSerializer()).serializeToString(tr_elements[jj]);
                            tb.trigger('create');
                        }
                    }
                } else {
                    $("#firstname-" + racerid).text(new_firstname);
                    var ln = $("#lastname-" + racerid);
                    ln.text(new_lastname);
                    ln.attr("data-exclude", exclude);
                    ln.parents('tr').toggleClass('exclude-racer', exclude == 1);
                    $("#car-number-" + racerid).text(new_carno);

                    $('#class-' + racerid).attr('data-rankid', new_rankid);
                    $('#class-' + racerid).text(new_classname);
                    $('#rank-' + racerid).text(new_rankname);
                }

                sort_checkin_table();
            },
           });
}

function show_racer_photo_modal(racerid) {
  $("#racer_photo_name").text($('#firstname-' + racerid).text() + ' ' + $('#lastname-' + racerid).text());
  show_modal("#racer_photo_modal", function() {
      take_snapshot(racerid);
      return false;
  });

  Webcam.set({
	  width: 320,
	  height: 240,
	  dest_width: 640,
	  dest_height: 480});
  Webcam.attach('#preview');
}

function take_snapshot(racerid) {
    console.log("take_snapshot: racerid = " + racerid);  // TODO
  Webcam.snap( function(data_uri) {
	  // detect image format from within image_data_uri
	  var image_fmt = '';
	  if (data_uri.match(/^data\:image\/(\w+)/))
		  image_fmt = RegExp.$1;
	  else
		  throw "Cannot locate image format in Data URI";
		
	  // extract raw base64 data from Data URI
	  var raw_image_data = data_uri.replace(/^data\:image\/\w+\;base64\,/, '');
		
	  // create a blob and decode our base64 to binary
	  var blob = new Blob( [ Webcam.base64DecToArr(raw_image_data) ], {type: 'image/'+image_fmt} );
		
	  // stuff into a form, so servers can easily receive it as a standard file upload
	  var form_data = new FormData();
	  form_data.append('action', 'upload-photo');
      form_data.append('racerid', racerid);
	  form_data.append('photo', blob, 'photo'+"."+image_fmt.replace(/e/, '') );

      // Testing for <failure> elements occurs in dashboard-ajax.js
      $.ajax(g_action_url,
             {type: 'POST',
              data: form_data,
              cache: false,
              contentType: false,
              processData: false,
              success: function(data) {
                  var photo_url_element = data.getElementsByTagName('photo-url');
                  if (photo_url_element.length > 0) {
                      $("#photo-" + racerid + " img").attr(
                          'src', photo_url_element[0].childNodes[0].nodeValue);
                  }
              }
             });

      close_modal("#racer_photo_modal");
  });
}

function close_racer_photo_modal() {
    close_modal("#racer_photo_modal");
}

function compare_first(a, b) {
  if (a[0] == b[0])
      return 0;
  if (a[0] < b[0])
      return -1;
   return 1;
}


function sorting_key(row) {
  if (g_order == 'den') {
      // class, lastname, firstname
      return [row.getElementsByClassName('sort-class')[0].innerHTML,
              row.getElementsByClassName('sort-lastname')[0].innerHTML,
              row.getElementsByClassName('sort-firstname')[0].innerHTML]
  } else if (g_order == 'car') {
      // carnumber (numeric), lastname, firstname
      return [parseInt(row.children('.sort-car-number')[0].innerHTML),
              row.getElementsByClassName('sort-lastname')[0].innerHTML,
              row.getElementsByClassName('sort-firstname')[0].innerHTML]
  } else /* 'name' */ {
      // lastname, firstname
      return [row.getElementsByClassName('sort-lastname')[0].innerHTML,
              row.getElementsByClassName('sort-firstname')[0].innerHTML]
  }
}

function sort_checkin_table() {
	row_array = [];
    rows = $(".main_table tbody").get(0).getElementsByTagName('tr');

	for (var j = 0; j < rows.length; ++j) {
	    row_array[row_array.length] = [sorting_key(rows[j]), rows[j]];
	}

	row_array.sort(compare_first);

	tb = $(".main_table tbody").get(0);

	for (var j = 0; j < row_array.length; ++j) {
        row_array[j][1].classList.remove('d' + (j & 1));
        row_array[j][1].classList.add('d' + ((j + 1) & 1));
	    tb.appendChild(row_array[j][1]);
	}

	delete row_array;
}