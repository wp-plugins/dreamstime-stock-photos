
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


  dt_search = function(keywords)
  {
    dt_loading('open');
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {action: 'ajxSearch', keywords: keywords}
    }).done(function(data){
      dt_loading('close');
      $('#dt_search_tab').html(data);
    });
  }

  dt_review = function(action)
  {
    dt_loading('open');
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {action: 'ajxReview', data: action}
    }).done(function(data){
      dt_loading('close');
      $('.review-note').remove();
    });
  }

  initialSearch = function(lastKeywords) {
    //search by post title
    var postTitle = window.parent.document.getElementById('title');
    var keywords = '';
    postTitle = $.trim($(postTitle).val());
    if(postTitle.length) {
      postTitle = $('<div/>').html(postTitle).text(); //strip html tags
      keywords = postTitle;
    } else {
      //search post by content
      var postContent = _getContentFromActiveEditor();
      keywords = _getMostFrequentlyWords(postContent);
    }
    if(keywords != lastKeywords || typeof lastKeywords === 'undefined') {
      $('#keywords').val(keywords);
      dt_search(keywords);
    }
  }

  _getContentFromActiveEditor = function() {
    var editorWrap = window.parent.document.getElementById('wp-content-wrap');
    var isTextEditor = $(editorWrap).hasClass('html-active');
    var content = '';
    if(isTextEditor) {
      content = $(editorWrap).find('#content').val();
    } else {
      content = $(editorWrap).find('#content_ifr').contents().find('body#tinymce').html();
    }
    return _stripPostContent(content);
  }

  _stripPostContent = function(postContent) {
    //strip dreamstime images credits
    postContent = postContent.replace(/<dd[\s\S]+class="wp-caption-dd"[\s\S]+<\/dd>/igm, '');

    //strip [shortcode ...] lorem ipsum [/shortcode] and [shortcode ...]
    postContent = postContent.replace(/\[[^\[]+\[\/[a-z]+\]|\[[^\]]+\]/igm, '');

    //strip html tags
    postContent = $('<div/>').html(postContent).text();

    //replace new lines with spaces
    postContent = postContent.replace(/\n/gi,' ');

    return postContent;
  }

  _getMostFrequentlyWords = function(postContent) {
    var contentArr = postContent.split(' ');
    var keywords = [];
    var values = [];
    $.each(contentArr, function(index, value){
      value = value.replace(/[^a-zA-Z]+/g, '');
      if(value.length >= 4) {
        var re = new RegExp(value, 'g');
        var matched = postContent.match(re);
        if(values.indexOf(value) == -1) {
          values.push(value);
          var count = matched.length;
          keywords.push({count: count, value: value});
        }
      }
    });
    //order by count desc
    keywords.sort(function(a, b){
      return b.count - a.count;
    });

    var keywordsArr = [];
    if(keywords.length >= 3) {
      $.each(keywords, function(index, obj){
        if(index < 5){
          keywordsArr.push(obj.value);
        }
      });
    }

    return keywordsArr.join(' ');
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

    /**
     * Daryl Koopersmith:
     * wp.media.editor is used to manage instances of editor-specific media managers.
     * If you're looking to trigger an event when opening the default media modal,
     * you'll want to grab a reference to the media manager by calling wp.media.editor.add('content').
     * We're calling "add" here instead of "get" to make sure the modal exists, because "get" may
     * return undefined (and don't worry, "add" only creates the instance once).
     * You can then call the .on method on that object and your code will run just fine.
     */

    $('a.insert-media').click(function(){
      var mediaModal = wp.media.editor.get(wpActiveEditor);
      if(mediaModal && typeof mediaModal != 'undefined') {
        if(mediaModal.state().id == 'iframe:dreamstime'){
          if($('.media-iframe > iframe').contents().find('#dt_tabs').length == 0 || true) {//forcing ...
            mediaModal.setState('insert');
            mediaModal.setState('iframe:dreamstime');
          }
        }
      }
    });

    $('#dreamstime-media-button').click(function(){
      var mediaModal = wp.media.editor.open(wpActiveEditor);
      if(mediaModal.state().id == 'iframe:dreamstime'){
        if($('.media-iframe > iframe').contents().find('#dt_tabs').length == 0 || true) { //forcing ...
          mediaModal.setState('insert');
          mediaModal.setState('iframe:dreamstime');
        }
      } else {
        mediaModal.setState('iframe:dreamstime');
      }
    });

    $(document.body).on('click', 'a.wp-post-thumbnail' ,function(event){
      var thumbnailId = parseInt($(event.target).attr('id').substr($(event.target).attr('id').lastIndexOf('-') + 1));
      var alt = $('tr.post_excerpt textarea').val();
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'ajxSetPostThumbnailAlt',
          alt: alt,
          thumbnail_id: thumbnailId
        },
        dataType: 'html'
      })
    });
  });

}( jQuery ));