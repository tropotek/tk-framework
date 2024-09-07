/**
 * Tk table javascript
 *
 *
 */

jQuery(function ($) {

  tkRegisterInit(function () {
    let table = $(this);

    // Class: \Tt\Table
    // Table limit on-change event
    $('.tk-limit select', table).change(function (e) {
      if ($(this).val() == 0 && $(this).data('total') > 1000) {
        if (!confirm('WARNING: There are large number of records, page load time may be slowed.')) return false;
      }
      const searchParams = new URLSearchParams(location.search);
      searchParams.set($(this).data('name'), $(this).val());
      searchParams.delete($(this).data('page'));
      location.search = searchParams.toString();
      return false;
    });


    // Class: \Tt\Table\Cell\RowSelect
    $('.tk-tcb-head', table).on('change', function(e) {
      let cbh = $(this);
      let name = cbh.attr('name').match(/([a-zA-Z0-9]+)_all/i)[1];
      let list = $(`input[name^="${name}"]`, table);
      list.prop('checked', cbh.prop('checked'));
    }).trigger('change');


    // Class: \Tt\Table\Action\Select
    function updateBtn(btn) {
      if (!btn.data('selectedOnly')) return;
      var rsName = btn.data('rowSelect');
      btn.prop('disabled', false);
      if(!$(`input[name^="${rsName}"]:checked`, table).length) {
        btn.prop('disabled', true);
      }
    }
    $('.tk-action-select', table).each(function () {
      var btn = $(this);
      var rsName = btn.data('rowSelect');
      btn.data('selectedOnly', btn.prop('disabled'))
      btn.on('click', function () {
        return $(`input[name^="${rsName}"]:checked`, table).length > 0;
      });
      btn.closest('.tk-table').on('change', `input[name^="${rsName}"]`, function () {
        updateBtn(btn);
      });
      updateBtn(btn);
    });

  });

});


