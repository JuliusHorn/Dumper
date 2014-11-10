var triggerContext = function($trigger)
{
    var $ele = $trigger.nextAll('.dumper-sub').eq(0);

    if ($ele.hasClass('dumper-show')) {
        $ele.removeClass('dumper-show');
        $(document.body).css('background', '#222');
    } else {
        $('.dumper-sub').removeClass('dumper-show');
        $ele.addClass('dumper-show');
        $(document.body).css('background', '#222');
    }
}

$('.dumper-sub-trigger').on('contextmenu', function($e) {
    $e.preventDefault();
    triggerContext($(this));

}).click(function($e) {
    $e.preventDefault();

    var $me  = $(this);
    var $ele = $(this).nextAll('.dumper-sub').eq(0);

    if ($me.hasClass('inactive')) {
        $me.removeClass('inactive');
        $ele.slideDown(150);
    } else {
        $me.addClass('inactive');
        $ele.slideUp(150);
    }
});

$(document).click(function($e) {
    if (
        !$($e.target).hasClass('dumper-sub-trigger') &&
        !$($e.target).parent().hasClass('dumper-sub-trigger') &&
        !$($e.target).hasClass('dumper-recursive')
    ) {
        $('.dumper-sub').removeClass('dumper-show');
        $(document.body).css('background', '#222');
    }
});

$('.dumper-recursive').click(function($e) {
    triggerContext($($($e.target).attr('href')));
});

$('#dumper-spoiler').click(function($e) {
    $('.dumper-trace-codeblock').stop().toggle(200);
})