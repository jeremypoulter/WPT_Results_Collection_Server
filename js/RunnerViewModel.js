/// <reference path="knockout-3.3.0.debug.js" />
/// <reference path="NewReferenceViewModel.js" />
/// <reference path="NewReportViewModel.js" />

function RunnerViewModel(appViewModel, resultsViewModel)
{
    var self = this;

    // Data
    self.fetching = ko.observable(false);

    self.resultsViewModel = resultsViewModel;
    self.session = ko.observable(false);

    // Derived data
    self.sessionList = ko.pureComputed(function () { return resultsViewModel.sessionList() });
    self.sessionListValid = ko.pureComputed(function () { return resultsViewModel.sessionListValid() });

    self.sessionListConnected = ko.computed(function () {
        return ko.utils.arrayFilter(resultsViewModel.sessionList(), function (item) {
            return item.status() == "connected";
        });
    });

    // Behaviours
    self.goToSession = function (session) { location.hash = 'runner/' + session.id(); };

    appViewModel.isRunner.subscribe(function (selected)
    {
        if (selected) {
            self.resultsViewModel.updateSessionList();
        } else {
            self.session(false);
        }
    });
}
