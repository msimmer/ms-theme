
(($) ->

  ORIGIN = window.location.origin
  DEBOUNCE_SPEED = 60
  TITLE_TYPE = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].join(',')
  TEXT_TYPE = ['.p', 'p', 'li', 'td'].join(',')
  PRELOAD_IMAGES = []

  $ ->

    resizeType = ->
      $(TITLE_TYPE).fitText(1.7)
      $(TEXT_TYPE).fitText(3.5)

    resizeHomeButton = ->
      ref = $('.nav__main li').height()
      $('[title=index]').css
        height: ref - 15
        width: ref - 15

    timer = null
    afterResize = (args) ->
      clearTimeout timer
      timer = setTimeout (=> args.forEach (arg) => arg.call(@)), DEBOUNCE_SPEED

    preload = ->
      PRELOAD_IMAGES.forEach (url) ->
        img = new Image
        img.src = url

    $(window).on 'resize', () -> afterResize([resizeHomeButton])

    resizeType()
    resizeHomeButton()
    preload()

    $('body').addClass('ready')

) jQuery
