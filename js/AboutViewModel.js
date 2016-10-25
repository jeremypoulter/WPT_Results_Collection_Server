/// <reference path="knockout-3.3.0.debug.js" />
/// <reference path="knockout.mapping-latest.debug.js" />

function AboutViewModel(appViewModel) {
    // Data
    var self = this;
    ko.mapping.fromJS({
        'version': 'unknown',
        'date': 'unknown',
        'system': 'unknown',
        'kernel': 'unknown',
        'host': 'unknown',
        'ip': 'unknown',
        'uptime': 'unknown',
        'http_server': 'unknown',
        'php': 'unknown',
        'php_modules': [],
        'zend': 'unknown',
        'hostbyaddress': 'unknown',
        'http_proto': 'unknown',
        'http_mode': 'unknown',
        'http_port': 'unknown'
    }, {}, self);

    self.fetching = ko.observable(false);

    self.php_module_list = ko.pureComputed(function () {
        return self.php_modules().join(', ');
    });

    self.update = function () {
        if (appViewModel.endpoints.about) {
            self.fetching(true);
            $.get(appViewModel.endpoints.about, function (data) {
                ko.mapping.fromJS(data, self);
                self.fetching(false);
            }, 'json');
        }
    };

    appViewModel.isAbout.subscribe(function (selected) {
        if (selected) {
            self.update();
        }
    });

}
