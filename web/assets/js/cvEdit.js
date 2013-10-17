$(document).ready(function() {
        $('.popup-marker').popover({
            title: 'Saved',
            content:'all changes applied in database',
            placement:'left',
            delay:{show:1000,hide:250},
            html: true,
            trigger: 'manual'
        }).click(function(e) {
                $('.popup-marker').not(this).popover('hide');
                $(this).popover('show');
            });
        $(document).click(function(e) {
            if (!$(e.target).is('.popup-marker')) {
                $('.popup-marker').popover('hide');
            }
        });
        $('.div-hide').bind('click',function(){
            toggleDiv(this);
        });
        restoreDiv();
    }
);
function toggleDiv(e){

    $('#div'+ $(e).attr('id')).slideToggle(100);
    $(e).toggleClass('btn-primary');
    if($(e).hasClass('btn-primary') && $(e).attr('id') != '-1'){
        localStorage.setItem("cvmkr_sect:"+$(e).attr('id'),$(e).attr('id'));
    }else{
        localStorage.removeItem("cvmkr_sect:"+$(e).attr('id'));
    }

}
function restoreDiv(){
    for(item in localStorage){
          toggleDiv($('#'+localStorage.getItem(item)));
    }
}
