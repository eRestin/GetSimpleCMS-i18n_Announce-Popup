$(function() {
    $(".date").datepicker({
        dateFormat: $.datepicker.TIMESTAMP,
        showOn: "button",
        buttonImage: "/plugins/i18n_announce/images/83-calendar.png",
        buttonImageOnly: true,
        onSelect: function(dateText, inst) {
            var date = $.datepicker.parseDate(inst.settings.dateFormat || $.datepicker._defaults.dateFormat, dateText, inst.settings);
            var dateLabel = $(this).data("label");
            var dateText = $.datepicker.formatDate("d M yy", date, inst.settings);
            $("#" + dateLabel).html(dateText);
        },
        beforeShowDay: function(caldate) {
            return [true, '', '']; // Avoiding a bug that doesn't allow to select today
            Today = new Date;
            if (caldate < Today) {
                return [false, '', 'Date is in the past'];
            }
            return [true, '', ''];
        }
    });
});
