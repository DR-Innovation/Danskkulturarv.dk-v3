const showWidth = () => {
    if (jQuery(".width_option").hasClass('hideme'))
        jQuery(".width_option").removeClass('hideme');
    else
        jQuery(".width_option").addClass('hideme');
}