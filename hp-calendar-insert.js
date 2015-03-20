(function ($) {
  $(function () {
    $('.hp-calendar').each(function () {
      var $this = $(this);
      $this.HPCalendar({
        strings: HP_Calendar_data.strings,
        info_container: $this.siblings('.hp-calendar-info'),
        api: HP_Calendar_data.ajaxurl,
        request_action: 'hp_calendar_request',
        clinic_filter: $this.data('clinics') || '',
        default_service: $this.data('default_service') || ''
      });
    });
  });
})(jQuery);
