// assets/js/jalali-picker.js
// لود Persian Datepicker از CDN و اعمال روی همه input[type=date]

(function() {
    // لود CSS
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css';
    document.head.appendChild(link);

    // لود jQuery (اگه نیست)
    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }

    function initPickers() {
        // تبدیل همه input[type=date] به text و اعمال picker
        var inputs = document.querySelectorAll('input[type="date"]');
        inputs.forEach(function(input) {
            // مقدار میلادی فعلی رو نگه‌دار
            var currentVal = input.value; // مثل 2026-06-08

            // تبدیل به شمسی برای نمایش اولیه
            var jalaliVal = '';
            if (currentVal) {
                jalaliVal = gregorianToJalali(currentVal);
            }

            // یه hidden input برای ارسال مقدار میلادی به سرور
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = input.name;
            hidden.value = currentVal;

            // تبدیل input اصلی به text
            input.type = 'text';
            input.name = input.name + '_jalali_display';
            input.value = jalaliVal;
            input.readOnly = true;
            input.style.cursor = 'pointer';
            input.placeholder = 'انتخاب تاریخ';

            input.parentNode.insertBefore(hidden, input.nextSibling);

            // اعمال picker
            $(input).persianDatepicker({
                format: 'YYYY/MM/DD',
                initialValue: !!jalaliVal,
                autoClose: true,
                onSelect: function(unix) {
                    // تبدیل به میلادی و ذخیره در hidden
                    var j = new persianDate(unix);
                    var g = j.toCalendar('gregorian');
                    var gStr = g.year() + '-' +
                        String(g.month()).padStart(2,'0') + '-' +
                        String(g.date()).padStart(2,'0');
                    hidden.value = gStr;

                    // اگه onchange داشت اجراش کن
                    if (input.getAttribute('data-onchange') === 'submit') {
                        input.closest('form').submit();
                    }
                }
            });

            // اگه input قبلاً onchange داشت
            var onchangeAttr = input.getAttribute('onchange');
            if (onchangeAttr && onchangeAttr.includes('submit')) {
                input.setAttribute('data-onchange', 'submit');
                input.removeAttribute('onchange');
            }
        });
    }

    // تبدیل ساده میلادی به شمسی (برای نمایش اولیه)
    function gregorianToJalali(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length < 3) return '';
        var gy = parseInt(parts[0]);
        var gm = parseInt(parts[1]);
        var gd = parseInt(parts[2]);

        var g_d_no = 365*gy + Math.floor((gy+3)/4) - Math.floor((gy+99)/100) + Math.floor((gy+399)/400);
        var gDays = [31,28,31,30,31,30,31,31,30,31,30,31];
        for (var i = 0; i < gm-1; i++) g_d_no += gDays[i];
        if (gm > 2 && ((gy%4===0 && gy%100!==0) || gy%400===0)) g_d_no++;
        g_d_no += gd - 1;

        var j_d_no = g_d_no - 79;
        var j_np = Math.floor(j_d_no/12053); j_d_no %= 12053;
        var jy = 979 + 33*j_np + 4*Math.floor(j_d_no/1461);
        j_d_no %= 1461;
        if (j_d_no >= 366) { jy += Math.floor((j_d_no-1)/365); j_d_no = (j_d_no-1)%365; }
        var jDays = [31,31,31,31,31,31,30,30,30,30,30,29];
        var jm = 0;
        for (jm = 0; jm < 11 && j_d_no >= jDays[jm]; jm++) j_d_no -= jDays[jm];
        return jy + '/' + String(jm+1).padStart(2,'0') + '/' + String(j_d_no+1).padStart(2,'0');
    }

    // لود dependencies
    if (typeof jQuery === 'undefined') {
        loadScript('https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js', function() {
            loadScript('https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js', function() {
                loadScript('https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js', function() {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initPickers);
                    } else {
                        initPickers();
                    }
                });
            });
        });
    } else {
        loadScript('https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js', function() {
            loadScript('https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js', function() {
                initPickers();
            });
        });
    }
})();