describe("#confirm", function() {
    var box;

    before(function() {
        bootbox.animate(false);
    });

    after(function() {
        $(".bootbox")
        .modal('hide')
        .remove();
    });

    describe("with one argument", function() {
        before(function() {
            box = bootbox.confirm("Hello world!");
        });

        it("shows the expected body copy", function() {
            assert.equal(
                box.find(".modal-body").html(),
                "Hello world!"
            );
        });

        it("shows an OK button", function() {
            assert.equal(box.find("a:first").html(), "OK");
        });

        it("shows a Cancel button", function() {
            assert.equal(box.find("a:last").html(), "Cancel");
        });

        it("does not apply the primary class to the cancel button", function() {
            assert.isFalse(box.find("a:last").hasClass("btn-primary"));
        });

        it("has focus on the OK button", function() {
            assert.isTrue(box.find("a:first").is(":focus"));
        });

        it("applies the primary class to the OK button", function() {
            assert.isTrue(box.find("a:first").hasClass("btn-primary"));
        });
    });

    describe("with two arguments", function() {
        describe("where the second argument is a string", function() {
            before(function() {
                box = bootbox.confirm("Hello world!", "Foo");
            });

            it("shows the expected body copy", function() {
                assert.equal(
                    box.find(".modal-body").html(),
                    "Hello world!"
                );
            });

            it("shows an OK button", function() {
                assert.equal(box.find("a:first").html(), "OK");
            });

            it("shows the custom Cancel label", function() {
                assert.equal(box.find("a:last").html(), "Foo");
            });
        });

        describe("where the second argument is a function", function() {
            before(function() {
                box = bootbox.confirm("Hello world!", function() { });
            });

            it("shows an OK button", function() {
                assert.equal(box.find("a:first").html(), "OK");
            });

            it("shows a Cancel button", function() {
                assert.equal(box.find("a:last").html(), "Cancel");
            });
        });
    });

    describe("with three arguments", function() {
        describe("where the third argument is a string", function() {
            before(function() {
                box = bootbox.confirm("Hello world!", "Foo", "Bar");
            });

            it("shows the expected body copy", function() {
                assert.equal(
                    box.find(".modal-body").html(),
                    "Hello world!"
                );
            });

            it("shows the custom OK label", function() {
                assert.equal(box.find("a:first").html(), "Bar");
            });

            it("applies the primary class to the custom OK button", function() {
                assert.isTrue(box.find("a:first").hasClass("btn-primary"));
            });

            it("shows the custom Cancel label", function() {
                assert.equal(box.find("a:last").html(), "Foo");
            });
        });

        describe("where the third argument is a function", function() {
            before(function() {
                box = bootbox.confirm("Hello world!", "Foo", function() { });
            });

            it("shows the default OK label", function() {
                assert.equal(box.find("a:first").html(), "OK");
            });
        });
    });

    describe("with four arguments", function() {
        before(function() {
            box = bootbox.confirm("Hello world!", "Foo", "Bar", function() {});
        });

        it("shows the expected body copy", function() {
            assert.equal(
                box.find(".modal-body").html(),
                "Hello world!"
            );
        });

        it("shows the custom OK label", function() {
            assert.equal(box.find("a:first").html(), "Bar");
        });

        it("shows the custom Cancel label", function() {
            assert.equal(box.find("a:last").html(), "Foo");
        });
    });

    describe("with five arguments", function() {
        it("throws an error", function() {
            assert.throws(function() {
                bootbox.confirm(1, 2, 3, 4, 5);
            });
        });
    });
    
    describe("with a callback", function() {
        describe("when dismissing the dialog by clicking OK", function() {
            var result;
            before(function() {
                box = bootbox.confirm("Sure?", function(cbResult) {
                    result = cbResult;
                });
            });

            it("should invoke the callback with a value of true", function() {
                box.find("a:first").trigger('click');
                assert.isTrue(result);
            });

            it("should close the dialog", function() {
                assert.isTrue(box.is(":hidden"));
            });
        });

        describe("when dismissing the dialog by clicking Cancel", function() {
            var result;
            before(function() {
                box = bootbox.confirm("Sure?", function(cbResult) {
                    result = cbResult;
                });
            });

            it("should invoke the callback with a value of true", function() {
                box.find("a:last").trigger('click');
                assert.isFalse(result);
            });

            it("should close the dialog", function() {
                assert.isTrue(box.is(":hidden"));
            });
        });

        describe("when pressing escape", function() {
            var called = false;
            before(function() {
                box = bootbox.confirm("Sure?", function(cbResult) {
                    called = true;
                });
            });

            it("should not invoke the callback", function() {
                var e = jQuery.Event("keyup.modal", {which: 27});
                $(document).trigger(e);

                assert.isFalse(called);
            });

            it("should not close the dialog", function() {
                assert.isFalse(box.is(":hidden"));
            });
        });
    });
});
