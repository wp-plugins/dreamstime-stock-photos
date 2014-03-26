


(function( $ ){

  dt_tab = function(index)
  {
    index = index - 1;
    $( "#dt_tabs" ).tabs({ active: index });
  }

  dt_error = function(error)
  {
    $('#error').html( error );
    $('#error').dialog('open');
  }

  dt_loading = function(action)
  {
    $('#loading').dialog(action);
  }

  dt_dialog = function (container, action, data)
  {
    var dialog = $('#'+container).dialog(action);
    if(data && data != undefined)
      $('#'+container).html(data);

    return dialog;
  }

  dt_checkUsername = function(username)
  {
    dt_loading('open');
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {action: 'checkUsername', username: username},
      dataType: 'html'
    }).done(function(data){
        dt_loading('close');
        $('#availability').html(data);
      });
  }

  setUsername = function(username)
  {
    $('#username').val(username);
  }

  dt_getImage = function(image)
  {
    dt_loading('open');
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {action: 'getImage', image: image},
      dataType: 'html'
    }).done(function(data){
        dt_loading('close');
        var dialog = dt_dialog('image', 'open', data);
      });
  }

  dt_downloadImage = function(image, size)
  {
    dt_loading('open');
    var data = {action: 'downloadImage', image: image};
    if(size && size != undefined) {
      data.size = size;
    }

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: data,
      dataType: 'html'
    }).done(function(data){
        dt_loading('close');
        dt_dialog('image', 'open', data);
        dt_refreshAccountInfo();
      });
  }

  dt_login = function()
  {
    dt_dialog('dt_login', 'open');
    $('#login_btn').click(function(){
      dt_loading('open');
      var data = $('#login-form').serialize();
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data,
        dataType: 'json'
      }).done(function(data){
          dt_loading('close');
          dt_dialog('dt_login', 'close');
          dt_getImage($('.dt_image').attr('rel'));
          $('#dt_search_tab').html(data.search);
          $('#dt_my_account_tab').html(data.account);
        });
    });
  }

  dt_refreshAccountInfo = function()
  {
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {action: 'refreshAccountInfo'},
      dataType: 'html'
    }).done(function(data){
        $('.account_info').html(data);
      });
  }

  dt_toggleReferral = function(state)
  {
    dt_loading('open');
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {action: 'toggleReferral', state: state}
    }).done(function(data){
        dt_loading('close');
      });
  }


  dt_more = function(container_id, params)
  {

    var container = $('#'+container_id);
    $('#'+container_id).dtMore({
      'items': '.dt_image_th',
      'contentPage': ajaxurl,
      'contentData': params,
      'total_items': container.attr('rel'),
      'beforeLoad': function(){
        container.children('.dt_clear').hide();
        container.children('.dt_progressbar').show();
      },
      'afterLoad': function(data) {
        container.children('.dt_clear').show();
        container.children('.dt_progressbar').hide();
      }
    });
  }




  $.fn.dtMore = function(options)
  {
    var defaults = {
      more :        '.more',
      items:        '.items',
      total_items:  20
    };

    var opts = $.extend({}, defaults, options);

    return this.each(function() {
      var container = $(this);
      var more = container.find(opts.more);
      more.click(function(){
        $.fn.dtMore.loadContent(container, opts);
      });
    });
  };

  $.fn.dtMore.loadContent = function(obj, opts){
    if (opts.beforeLoad != null){
      opts.beforeLoad();
    }
    $.ajax({
      type: 'POST',
      url: opts.contentPage,
      data: opts.contentData,
      dataType: 'html'
    }).done(function(data){
        var more = obj.find(opts.more);
        more.parent().before(data);

        if (opts.afterLoad != null){
          opts.afterLoad(data);
        }
        if(obj.children(opts.items).length >= opts.total_items) {
          more.remove();
        }
      }).fail(function(data){
        opts.afterLoad(data);
      });
  };


  $(document).ready(function(){
    $('a.insert-media').click(function(){
      var mediaModal = wp.media.editor.get(wpActiveEditor);
      if(mediaModal && mediaModal != undefined) {
        if(mediaModal.state().id == 'iframe:dreamstime'){
          if($('.media-iframe > iframe').contents().find('#dt_tabs').length == 0) {
            mediaModal.setState('insert');
            mediaModal.setState('iframe:dreamstime');
          }
        }
      }
    });

    $('#dreamstime-media-button').click(function(){
      var mediaModal = wp.media.editor.open(wpActiveEditor);
      if(mediaModal.state().id == 'iframe:dreamstime'){
        if($('.media-iframe > iframe').contents().find('#dt_tabs').length == 0) {
          mediaModal.setState('insert');
          mediaModal.setState('iframe:dreamstime');
        }
      } else {
        mediaModal.setState('iframe:dreamstime');
      }
    });

  });

})( jQuery );