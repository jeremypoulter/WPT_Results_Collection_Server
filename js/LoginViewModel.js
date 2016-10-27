/// <reference path="knockout-3.3.0.debug.js" />
/// <reference path="knockout.mapping-latest.debug.js" />

function LoginViewModel(template)
{
    // Data
    var self = this;
    self.template = template;

    self.username = ko.observable("");
    self.password = ko.observable("");
    self.valid = ko.pureComputed(function () {
        return self.username().length > 0 && self.password().length > 0;
    });

    self.complete = function ()
    {
        this.modal.close({
            username: self.username(),
            password: self.password()
        });
    };

    self.cancel = function ()
    {
        // Close the modal without passing any result data.
        this.modal.close();
    };
}
