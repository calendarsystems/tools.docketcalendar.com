
<script type="text/javascript">
jQuery('#searchtextbutton').click(function () {
    var $form=$('#researchform');
    jQuery.post($form.attr('action'), $form.serialize());
});

jQuery('#btnSearchHoliday').click(function () {
    var $form=$('#researchform');
    jQuery.post($form.attr('action'), $form.serialize());
});

jQuery('#btnSearchServices').click(function () {
    var $form=$('#researchform');
    jQuery.post($form.attr('action'), $form.serialize());
});

</script>