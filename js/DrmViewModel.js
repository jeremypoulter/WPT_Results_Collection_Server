/// <reference path="knockout-3.3.0.debug.js" />
/// <reference path="knockout.mapping-latest.debug.js" />

function DrmViewModel(appViewModel) {
    // Data
    var self = this;
    ko.mapping.fromJS({
        'userId': false,
        'sessionId': false,
        'message': false,
        'profile': false
    }, {}, self);

    self.fetching = ko.observable(false);

    self.update = function () {
        if (appViewModel.endpoints.drm) {
            self.fetching(true);
            $.get(appViewModel.endpoints.drm, function (data) {
                ko.mapping.fromJS(data, self);
            }, 'json').always(function () {
                self.fetching(false);
            });
        }
    };

    self.login = function () {
        showModal({
            viewModel: new LoginViewModel('DrmLogin', self.userId()),
            context: this // Set context so we don't need to bind the callback function
        }).then(function (result) {
            self.fetching(true);
            $.post(appViewModel.endpoints.drm, result,
            function (data) {
                ko.mapping.fromJS(data, self);
            }, 'json').fail(function () {
                self.userId(false);
                self.sessionId(false);
            }).always(function () {
                self.fetching(false);
            });
        });
    };

    self.logout = function () {
        self.fetching(true);
        $.ajax({
            url: appViewModel.endpoints.drm,
            type: 'DELETE'
        }).always(function () {
            self.update();
        });
    };

    appViewModel.isAbout.subscribe(function (selected) {
        if (selected) {
            self.update();
        }
    });

}
