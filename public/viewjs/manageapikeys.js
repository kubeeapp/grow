﻿var apiKeysTable = $('#apikeys-table').DataTable({
	'order': [[4, 'desc']],
	'columnDefs': [
		{ 'orderable': false, 'targets': 0 }
	]
});
$('#apikeys-table tbody').removeClass("d-none");
apiKeysTable.columns.adjust().draw();

var createdApiKeyId = GetUriParam('CreatedApiKeyId');
if (createdApiKeyId !== undefined)
{
	$('#apiKeyRow_' + createdApiKeyId).effect('highlight', {}, 3000);
}

$("#search").on("keyup", Delay(function()
{
	var value = $(this).val();
	if (value === "all")
	{
		value = "";
	}

	apiKeysTable.search(value).draw();
}, 200));

$(document).on('click', '.apikey-delete-button', function (e)
{
	var objectName = $(e.currentTarget).attr('data-apikey-apikey');
	var objectId = $(e.currentTarget).attr('data-apikey-id');

	bootbox.confirm({
		message: __t('Are you sure to delete API key "%s"?', objectName),
		closeButton: false,
		buttons: {
			confirm: {
				label: __t('Yes'),
				className: 'btn-success'
			},
			cancel: {
				label: __t('No'),
				className: 'btn-danger'
			}
		},
		callback: function(result)
		{
			if (result === true)
			{
				Grocy.Api.Delete('objects/api_keys/' + objectId, {},
					function(result)
					{
						window.location.href = U('/manageapikeys');
					},
					function(xhr)
					{
						console.error(xhr);
					}
				);
			}
		}
	});
});
