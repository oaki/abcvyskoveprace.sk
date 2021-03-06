/**
 * Copyright (c) 2008 Kelvin Luck (http://www.kelvinluck.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * .
 * $Id: jquery.datePicker.js 102 2010-09-13 14:00:54Z kelvin.luck $
 **/

Date.dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
Date.abbrDayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
Date.monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
Date.abbrMonthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
Date.firstDayOfWeek = 1;
Date.format = "dd/mm/yyyy";
Date.fullYearStart = "20";
(function() {
    function b(g, j) {
        Date.prototype[g] || (Date.prototype[g] = j)
    }

    b("isLeapYear", function() {
        var g = this.getFullYear();
        return g % 4 == 0 && g % 100 != 0 || g % 400 == 0
    });
    b("isWeekend", function() {
        return this.getDay() == 0 || this.getDay() == 6
    });
    b("isWeekDay", function() {
        return !this.isWeekend()
    });
    b("getDaysInMonth", function() {
        return [31, this.isLeapYear() ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][this.getMonth()]
    });
    b("getDayName", function(g) {
        return g ? Date.abbrDayNames[this.getDay()] : Date.dayNames[this.getDay()]
    });
    b("getMonthName", function(g) {
        return g ? Date.abbrMonthNames[this.getMonth()] : Date.monthNames[this.getMonth()]
    });
    b("getDayOfYear", function() {
        var g = new Date("1/1/" + this.getFullYear());
        return Math.floor((this.getTime() - g.getTime()) / 864E5)
    });
    b("getWeekOfYear", function() {
        return Math.ceil(this.getDayOfYear() / 7)
    });
    b("setDayOfYear", function(g) {
        this.setMonth(0);
        this.setDate(g);
        return this
    });
    b("addYears", function(g) {
        this.setFullYear(this.getFullYear() + g);
        return this
    });
    b("addMonths", function(g) {
        var j = this.getDate();
        this.setMonth(this.getMonth() + g);
        j > this.getDate() && this.addDays(-this.getDate());
        return this
    });
    b("addDays", function(g) {
        this.setTime(this.getTime() + g * 864E5);
        return this
    });
    b("addHours", function(g) {
        this.setHours(this.getHours() + g);
        return this
    });
    b("addMinutes", function(g) {
        this.setMinutes(this.getMinutes() + g);
        return this
    });
    b("addSeconds", function(g) {
        this.setSeconds(this.getSeconds() + g);
        return this
    });
    b("zeroTime", function() {
        this.setMilliseconds(0);
        this.setSeconds(0);
        this.setMinutes(0);
        this.setHours(0);
        return this
    });
    b("asString", function(g) {
        return (g || Date.format).split("yyyy").join(this.getFullYear()).split("yy").join((this.getFullYear() + "").substring(2)).split("mmmm").join(this.getMonthName(false)).split("mmm").join(this.getMonthName(true)).split("mm").join(l(this.getMonth() + 1)).split("dd").join(l(this.getDate())).split("hh").join(l(this.getHours())).split("min").join(l(this.getMinutes())).split("ss").join(l(this.getSeconds()))
    });
    Date.fromString = function(g, j) {
        var a = j || Date.format, c = new Date("01/01/1977"), e = 0, d = a.indexOf("mmmm");
        if (d > -1) {
            for (var f = 0; f < Date.monthNames.length; f++) {
                var h = g.substr(d, Date.monthNames[f].length);
                if (Date.monthNames[f] == h) {
                    e = Date.monthNames[f].length - 4;
                    break
                }
            }
            c.setMonth(f)
        } else {
            d = a.indexOf("mmm");
            if (d > -1) {
                h = g.substr(d, 3);
                for (f = 0; f < Date.abbrMonthNames.length; f++)if (Date.abbrMonthNames[f] == h)break;
                c.setMonth(f)
            } else c.setMonth(Number(g.substr(a.indexOf("mm"), 2)) - 1)
        }
        f = a.indexOf("yyyy");
        if (f > -1) {
            if (d < f)f += e;
            c.setFullYear(Number(g.substr(f, 4)))
        } else c.setFullYear(Number(Date.fullYearStart + g.substr(a.indexOf("yy"), 2)));
        a = a.indexOf("dd");
        if (d < a)a += e;
        c.setDate(Number(g.substr(a, 2)));
        if (isNaN(c.getTime()))return false;
        return c
    };
    var l = function(g) {
        g = "0" + g;
        return g.substring(g.length - 2)
    }
})();
(function(b) {
    function l(a) {
        this.ele = a;
        this.button = this.horizontalOffset = this.verticalOffset = this.horizontalPosition = this.verticalPosition = this.numSelected = this.numSelectable = this.selectMultiple = this.rememberViewedMonth = this.displayClose = this.closeOnSelect = this.showYearNavigation = this.endDate = this.startDate = this.displayedYear = this.displayedMonth = null;
        this.renderCallback = [];
        this.selectedDates = {};
        this.inline = null;
        this.context = "#dp-popup";
        this.settings = {}
    }

    function g(a) {
        if (a._dpId)return b.event._dpCache[a._dpId];
        return false
    }

    b.fn.extend({
        renderCalendar         : function(a) {
            a = b.extend({}, b.fn.datePicker.defaults, a);
            if (a.showHeader != b.dpConst.SHOW_HEADER_NONE)for (var c = b(document.createElement("tr")), e = Date.firstDayOfWeek; e < Date.firstDayOfWeek + 7; e++) {
                var d = e % 7, f = Date.dayNames[d];
                c.append(jQuery(document.createElement("th")).attr({
                    scope   : "col",
                    abbr    : f,
                    title   : f,
                    "class" : d == 0 || d == 6 ? "weekend" : "weekday"
                }).html(a.showHeader == b.dpConst.SHOW_HEADER_SHORT ? f.substr(0, 1) : f))
            }
            var h = b(document.createElement("table")).attr({cellspacing : 2}).addClass("jCalendar").append(a.showHeader != b.dpConst.SHOW_HEADER_NONE ? b(document.createElement("thead")).append(c) : document.createElement("thead"));
            c = b(document.createElement("tbody"));
            d = (new Date).zeroTime();
            d.setHours(12);
            f = a.month == undefined ? d.getMonth() : a.month;
            var k = a.year || d.getFullYear(), i = new Date(k, f, 1, 12, 0, 0);
            e = Date.firstDayOfWeek - i.getDay() + 1;
            if (e > 1)e -= 7;
            var o = Math.ceil((-1 * e + 1 + i.getDaysInMonth()) / 7);
            i.addDays(e - 1);
            for (var r = function(n) {
                return function() {
                    if (a.hoverClass) {
                        var p = b(this);
                        if (a.selectWeek)n && !p.is(".disabled") && p.parent().addClass("activeWeekHover"); else p.addClass(a.hoverClass)
                    }
                }
            }, s = function() {
                if (a.hoverClass) {
                    var n = b(this);
                    n.removeClass(a.hoverClass);
                    n.parent().removeClass("activeWeekHover")
                }
            }, t = 0; t++ < o;) {
                var q = jQuery(document.createElement("tr")), u = a.dpController ? i > a.dpController.startDate : false;
                for (e = 0; e < 7; e++) {
                    var m = i.getMonth() == f;
                    m = b(document.createElement("td")).text(i.getDate() + "").addClass((m ? "current-month " : "other-month ") + (i.isWeekend() ? "weekend " : "weekday ") + (m && i.getTime() == d.getTime() ? "today " : "")).data("datePickerDate", i.asString()).hover(r(u), s);
                    q.append(m);
                    a.renderCallback && a.renderCallback(m, i, f, k);
                    i = new Date(i.getFullYear(), i.getMonth(), i.getDate() + 1, 12, 0, 0)
                }
                c.append(q)
            }
            h.append(c);
            return this.each(function() {
                b(this).empty().append(h)
            })
        }, datePicker          : function(a) {
            if (!b.event._dpCache)b.event._dpCache = [];
            a = b.extend({}, b.fn.datePicker.defaults, a);
            return this.each(function() {
                var c = b(this), e = true;
                if (!this._dpId) {
                    this._dpId = b.event.guid++;
                    b.event._dpCache[this._dpId] = new l(this);
                    e = false
                }
                if (a.inline) {
                    a.createButton = false;
                    a.displayClose = false;
                    a.closeOnSelect = false;
                    c.empty()
                }
                var d = b.event._dpCache[this._dpId];
                d.init(a);
                if (!e && a.createButton) {
                    d.button = b('<a href="#" class="dp-choose-date" title="' + b.dpText.TEXT_CHOOSE_DATE + '">' + b.dpText.TEXT_CHOOSE_DATE + "</a>").bind("click", function() {
                        c.dpDisplay(this);
                        this.blur();
                        return false
                    });
                    c.after(d.button)
                }
                if (!e && c.is(":text")) {
                    c.bind("dateSelected", function(f, h) {
                        this.value = h.asString()
                    }).bind("change", function() {
                        if (this.value == "")d.clearSelected(); else {
                            var f = Date.fromString(this.value);
                            f && d.setSelected(f, true, true)
                        }
                    });
                    a.clickInput && c.bind("click", function() {
                        c.trigger("change");
                        c.dpDisplay()
                    });
                    e = Date.fromString(this.value);
                    this.value != "" && e && d.setSelected(e, true, true)
                }
                c.addClass("dp-applied")
            })
        }, dpSetDisabled       : function(a) {
            return j.call(this, "setDisabled", a)
        }, dpSetStartDate      : function(a) {
            return j.call(this, "setStartDate", a)
        }, dpSetEndDate        : function(a) {
            return j.call(this, "setEndDate", a)
        }, dpGetSelected       : function() {
            var a = g(this[0]);
            if (a)return a.getSelected();
            return null
        }, dpSetSelected       : function(a, c, e, d) {
            if (c == undefined)c = true;
            if (e == undefined)e = true;
            if (d == undefined)d = true;
            return j.call(this, "setSelected", Date.fromString(a), c, e, d)
        }, dpSetDisplayedMonth : function(a, c) {
            return j.call(this, "setDisplayedMonth", Number(a), Number(c), true)
        }, dpDisplay           : function(a) {
            return j.call(this, "display", a)
        }, dpSetRenderCallback : function(a) {
            return j.call(this, "setRenderCallback", a)
        }, dpSetPosition       : function(a, c) {
            return j.call(this, "setPosition", a, c)
        }, dpSetOffset         : function(a, c) {
            return j.call(this, "setOffset", a, c)
        }, dpClose             : function() {
            return j.call(this, "_closeCalendar", false, this[0])
        }, dpRerenderCalendar  : function() {
            return j.call(this, "_rerenderCalendar")
        }, _dpDestroy          : function() {
        }
    });
    var j = function(a, c, e, d, f) {
        return this.each(function() {
            var h = g(this);
            h && h[a](c, e, d, f)
        })
    };
    b.extend(l.prototype, {
        init                     : function(a) {
            this.setStartDate(a.startDate);
            this.setEndDate(a.endDate);
            this.setDisplayedMonth(Number(a.month), Number(a.year));
            this.setRenderCallback(a.renderCallback);
            this.showYearNavigation = a.showYearNavigation;
            this.closeOnSelect = a.closeOnSelect;
            this.displayClose = a.displayClose;
            this.rememberViewedMonth = a.rememberViewedMonth;
            this.numSelectable = (this.selectMultiple = a.selectMultiple) ? a.numSelectable : 1;
            this.numSelected = 0;
            this.verticalPosition = a.verticalPosition;
            this.horizontalPosition = a.horizontalPosition;
            this.hoverClass = a.hoverClass;
            this.setOffset(a.verticalOffset, a.horizontalOffset);
            this.inline = a.inline;
            this.settings = a;
            if (this.inline) {
                this.context = this.ele;
                this.display()
            }
        }, setStartDate          : function(a) {
            if (a)this.startDate = Date.fromString(a);
            if (!this.startDate)this.startDate = (new Date).zeroTime();
            this.setDisplayedMonth(this.displayedMonth, this.displayedYear)
        }, setEndDate            : function(a) {
            if (a)this.endDate = Date.fromString(a);
            if (!this.endDate)this.endDate = new Date("12/31/2999");
            if (this.endDate.getTime() < this.startDate.getTime())this.endDate = this.startDate;
            this.setDisplayedMonth(this.displayedMonth, this.displayedYear)
        }, setPosition           : function(a, c) {
            this.verticalPosition = a;
            this.horizontalPosition = c
        }, setOffset             : function(a, c) {
            this.verticalOffset = parseInt(a) || 0;
            this.horizontalOffset = parseInt(c) || 0
        }, setDisabled           : function(a) {
            $e = b(this.ele);
            $e[a ? "addClass" : "removeClass"]("dp-disabled");
            if (this.button) {
                $but = b(this.button);
                $but[a ? "addClass" : "removeClass"]("dp-disabled");
                $but.attr("title", a ? "" : b.dpText.TEXT_CHOOSE_DATE)
            }
            if ($e.is(":text"))$e.attr("disabled", a ? "disabled" : "")
        }, setDisplayedMonth     : function(a, c, e) {
            if (!(this.startDate == undefined || this.endDate == undefined)) {
                var d = new Date(this.startDate.getTime());
                d.setDate(1);
                var f = new Date(this.endDate.getTime());
                f.setDate(1);
                if (!a && !c || isNaN(a) && isNaN(c)) {
                    a = (new Date).zeroTime();
                    a.setDate(1)
                } else a = isNaN(a) ? new Date(c, this.displayedMonth, 1) : isNaN(c) ? new Date(this.displayedYear, a, 1) : new Date(c, a, 1);
                if (a.getTime() < d.getTime())a = d; else if (a.getTime() > f.getTime())a = f;
                d = this.displayedMonth;
                f = this.displayedYear;
                this.displayedMonth = a.getMonth();
                this.displayedYear = a.getFullYear();
                if (e && (this.displayedMonth != d || this.displayedYear != f)) {
                    this._rerenderCalendar();
                    b(this.ele).trigger("dpMonthChanged", [this.displayedMonth, this.displayedYear])
                }
            }
        }, setSelected           : function(a, c, e, d) {
            if (!(a < this.startDate || a.zeroTime() > this.endDate.zeroTime())) {
                var f = this.settings;
                if (f.selectWeek) {
                    a = a.addDays(-(a.getDay() - Date.firstDayOfWeek + 7) % 7);
                    if (a < this.startDate)return
                }
                if (c != this.isSelected(a)) {
                    if (this.selectMultiple == false)this.clearSelected(); else if (c && this.numSelected == this.numSelectable)return;
                    if (e && (this.displayedMonth != a.getMonth() || this.displayedYear != a.getFullYear()))this.setDisplayedMonth(a.getMonth(), a.getFullYear(), true);
                    this.selectedDates[a.asString()] = c;
                    this.numSelected += c ? 1 : -1;
                    e = "td." + (a.getMonth() == this.displayedMonth ? "current-month" : "other-month");
                    var h;
                    b(e, this.context).each(function() {
                        if (b(this).data("datePickerDate") == a.asString()) {
                            h = b(this);
                            if (f.selectWeek)h.parent()[c ? "addClass" : "removeClass"]("selectedWeek");
                            h[c ? "addClass" : "removeClass"]("selected")
                        }
                    });
                    b("td", this.context).not(".selected")[this.selectMultiple && this.numSelected == this.numSelectable ? "addClass" : "removeClass"]("unselectable");
                    if (d) {
                        f = this.isSelected(a);
                        $e = b(this.ele);
                        d = Date.fromString(a.asString());
                        $e.trigger("dateSelected", [d, h, f]);
                        $e.trigger("change")
                    }
                }
            }
        }, isSelected            : function(a) {
            return this.selectedDates[a.asString()]
        }, getSelected           : function() {
            var a = [], c;
            for (c in this.selectedDates)this.selectedDates[c] == true && a.push(Date.fromString(c));
            return a
        }, clearSelected         : function() {
            this.selectedDates = {};
            this.numSelected = 0;
            b("td.selected", this.context).removeClass("selected").parent().removeClass("selectedWeek")
        }, display               : function(a) {
            if (!b(this.ele).is(".dp-disabled")) {
                a = a || this.ele;
                var c = this;
                a = b(a);
                var e = a.offset(), d, f, h;
                if (c.inline) {
                    d = b(this.ele);
                    f = {id : "calendar-" + this.ele._dpId, "class" : "dp-popup dp-popup-inline"};
                    b(".dp-popup", d).remove();
                    h = {}
                } else {
                    d = b("body");
                    f = {id : "dp-popup", "class" : "dp-popup"};
                    h = {top : e.top + c.verticalOffset, left : e.left + c.horizontalOffset};
                    this._checkMouse = function(i) {
                        i = i.target;
                        for (var o = b("#dp-popup")[0]; ;)if (i == o)return true; else if (i == document) {
                            c._closeCalendar();
                            return false
                        } else i = b(i).parent()[0]
                    };
                    c._closeCalendar(true);
                    b(document).bind("keydown.datepicker", function(i) {
                        i.keyCode == 27 && c._closeCalendar()
                    })
                }
                if (!c.rememberViewedMonth) {
                    var k = this.getSelected()[0];
                    if (k) {
                        k = new Date(k);
                        this.setDisplayedMonth(k.getMonth(), k.getFullYear(), false)
                    }
                }
                d.append(b("<div></div>").attr(f).css(h).append(b("<h2></h2>"), b('<div class="dp-nav-prev"></div>').append(b('<a class="dp-nav-prev-year" href="#" title="' + b.dpText.TEXT_PREV_YEAR + '">&lt;&lt;</a>').bind("click", function() {
                    return c._displayNewMonth.call(c, this, 0, -1)
                }), b('<a class="dp-nav-prev-month" href="#" title="' + b.dpText.TEXT_PREV_MONTH + '">&lt;</a>').bind("click", function() {
                    return c._displayNewMonth.call(c, this, -1, 0)
                })), b('<div class="dp-nav-next"></div>').append(b('<a class="dp-nav-next-year" href="#" title="' + b.dpText.TEXT_NEXT_YEAR + '">&gt;&gt;</a>').bind("click", function() {
                    return c._displayNewMonth.call(c, this, 0, 1)
                }), b('<a class="dp-nav-next-month" href="#" title="' + b.dpText.TEXT_NEXT_MONTH + '">&gt;</a>').bind("click", function() {
                    return c._displayNewMonth.call(c, this, 1, 0)
                })), b('<div class="dp-calendar"></div>')).bgIframe());
                d = this.inline ? b(".dp-popup", this.context) : b("#dp-popup");
                this.showYearNavigation == false && b(".dp-nav-prev-year, .dp-nav-next-year", c.context).css("display", "none");
                this.displayClose && d.append(b('<a href="#" id="dp-close">' + b.dpText.TEXT_CLOSE + "</a>").bind("click", function() {
                    c._closeCalendar();
                    return false
                }));
                c._renderCalendar();
                b(this.ele).trigger("dpDisplayed", d);
                if (!c.inline) {
                    this.verticalPosition == b.dpConst.POS_BOTTOM && d.css("top", e.top + a.height() - d.height() + c.verticalOffset);
                    this.horizontalPosition == b.dpConst.POS_RIGHT && d.css("left", e.left + a.width() - d.width() + c.horizontalOffset);
                    b(document).bind("mousedown.datepicker", this._checkMouse)
                }
            }
        }, setRenderCallback     : function(a) {
            if (a != null) {
                if (a && typeof a == "function")a = [a];
                this.renderCallback = this.renderCallback.concat(a)
            }
        }, cellRender            : function(a, c) {
            var e = this.dpController, d = new Date(c.getTime());
            a.bind("click", function() {
                var f = b(this);
                if (!f.is(".disabled")) {
                    e.setSelected(d, !f.is(".selected") || !e.selectMultiple, false, true);
                    if (e.closeOnSelect) {
                        if (e.settings.autoFocusNextInput) {
                            var h = e.ele, k = false;
                            b(":input", h.form).each(function() {
                                if (k) {
                                    b(this).focus();
                                    return false
                                }
                                if (this == h)k = true
                            })
                        } else e.ele.focus();
                        e._closeCalendar()
                    }
                }
            });
            if (e.isSelected(d)) {
                a.addClass("selected");
                e.settings.selectWeek && a.parent().addClass("selectedWeek")
            } else e.selectMultiple && e.numSelected == e.numSelectable && a.addClass("unselectable")
        }, _applyRenderCallbacks : function() {
            var a = this;
            b("td", this.context).each(function() {
                for (var c = 0; c < a.renderCallback.length; c++) {
                    $td = b(this);
                    a.renderCallback[c].apply(this, [$td, Date.fromString($td.data("datePickerDate")), a.displayedMonth, a.displayedYear])
                }
            })
        }, _displayNewMonth      : function(a, c, e) {
            b(a).is(".disabled") || this.setDisplayedMonth(this.displayedMonth + c, this.displayedYear + e, true);
            a.blur();
            return false
        }, _rerenderCalendar     : function() {
            this._clearCalendar();
            this._renderCalendar()
        }, _renderCalendar       : function() {
            b("h2", this.context).html((new Date(this.displayedYear, this.displayedMonth, 1)).asString(b.dpText.HEADER_FORMAT));
            b(".dp-calendar", this.context).renderCalendar(b.extend({}, this.settings, {
                month          : this.displayedMonth,
                year           : this.displayedYear,
                renderCallback : this.cellRender,
                dpController   : this,
                hoverClass     : this.hoverClass
            }));
            if (this.displayedYear == this.startDate.getFullYear() && this.displayedMonth == this.startDate.getMonth()) {
                b(".dp-nav-prev-year", this.context).addClass("disabled");
                b(".dp-nav-prev-month", this.context).addClass("disabled");
                b(".dp-calendar td.other-month", this.context).each(function() {
                    var d = b(this);
                    Number(d.text()) > 20 && d.addClass("disabled")
                });
                var a = this.startDate.getDate();
                b(".dp-calendar td.current-month", this.context).each(function() {
                    var d = b(this);
                    Number(d.text()) < a && d.addClass("disabled")
                })
            } else {
                b(".dp-nav-prev-year", this.context).removeClass("disabled");
                b(".dp-nav-prev-month", this.context).removeClass("disabled");
                a = this.startDate.getDate();
                if (a > 20) {
                    var c = this.startDate.getTime(), e = new Date(c);
                    e.addMonths(1);
                    this.displayedYear == e.getFullYear() && this.displayedMonth == e.getMonth() && b(".dp-calendar td.other-month", this.context).each(function() {
                        var d = b(this);
                        Date.fromString(d.data("datePickerDate")).getTime() < c && d.addClass("disabled")
                    })
                }
            }
            if (this.displayedYear == this.endDate.getFullYear() && this.displayedMonth == this.endDate.getMonth()) {
                b(".dp-nav-next-year", this.context).addClass("disabled");
                b(".dp-nav-next-month", this.context).addClass("disabled");
                b(".dp-calendar td.other-month", this.context).each(function() {
                    var d = b(this);
                    Number(d.text()) < 14 && d.addClass("disabled")
                });
                a = this.endDate.getDate();
                b(".dp-calendar td.current-month", this.context).each(function() {
                    var d = b(this);
                    Number(d.text()) > a && d.addClass("disabled")
                })
            } else {
                b(".dp-nav-next-year", this.context).removeClass("disabled");
                b(".dp-nav-next-month", this.context).removeClass("disabled");
                a = this.endDate.getDate();
                if (a < 13) {
                    e = new Date(this.endDate.getTime());
                    e.addMonths(-1);
                    this.displayedYear == e.getFullYear() && this.displayedMonth == e.getMonth() && b(".dp-calendar td.other-month", this.context).each(function() {
                        var d = b(this), f = Number(d.text());
                        f < 13 && f > a && d.addClass("disabled")
                    })
                }
            }
            this._applyRenderCallbacks()
        }, _closeCalendar        : function(a, c) {
            if (!c || c == this.ele) {
                b(document).unbind("mousedown.datepicker");
                b(document).unbind("keydown.datepicker");
                this._clearCalendar();
                b("#dp-popup a").unbind();
                b("#dp-popup").empty().remove();
                a || b(this.ele).trigger("dpClosed", [this.getSelected()])
            }
        }, _clearCalendar        : function() {
            b(".dp-calendar td", this.context).unbind();
            b(".dp-calendar", this.context).empty()
        }
    });
    b.dpConst = {
        SHOW_HEADER_NONE  : 0,
        SHOW_HEADER_SHORT : 1,
        SHOW_HEADER_LONG  : 2,
        POS_TOP           : 0,
        POS_BOTTOM        : 1,
        POS_LEFT          : 0,
        POS_RIGHT         : 1,
        DP_INTERNAL_FOCUS : "dpInternalFocusTrigger"
    };
    b.dpText = {
        TEXT_PREV_YEAR   : "Previous year",
        TEXT_PREV_MONTH  : "Previous month",
        TEXT_NEXT_YEAR   : "Next year",
        TEXT_NEXT_MONTH  : "Next month",
        TEXT_CLOSE       : "Close",
        TEXT_CHOOSE_DATE : "Choose date",
        HEADER_FORMAT    : "mmmm yyyy"
    };
    b.dpVersion = "$Id: jquery.datePicker.js 102 2010-09-13 14:00:54Z kelvin.luck $";
    b.fn.datePicker.defaults = {
        month               : undefined,
        year                : undefined,
        showHeader          : b.dpConst.SHOW_HEADER_SHORT,
        startDate           : undefined,
        endDate             : undefined,
        inline              : false,
        renderCallback      : null,
        createButton        : true,
        showYearNavigation  : true,
        closeOnSelect       : true,
        displayClose        : false,
        selectMultiple      : false,
        numSelectable       : Number.MAX_VALUE,
        clickInput          : false,
        rememberViewedMonth : true,
        selectWeek          : false,
        verticalPosition    : b.dpConst.POS_TOP,
        horizontalPosition  : b.dpConst.POS_LEFT,
        verticalOffset      : 0,
        horizontalOffset    : 0,
        hoverClass          : "dp-hover",
        autoFocusNextInput  : false
    };
    if (b.fn.bgIframe == undefined)b.fn.bgIframe = function() {
        return this
    };
    b(window).bind("unload", function() {
        var a = b.event._dpCache || [], c;
        for (c in a)b(a[c].ele)._dpDestroy()
    })
})(jQuery);