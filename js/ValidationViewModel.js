/// <reference path="knockout-3.3.0.debug.js" />

function ValidationViewModel(appViewModel, resultsViewModel)
{
    var referenceListMapping =
    {
        key: function (data) {
            return ko.utils.unwrapObservable(data.id);
        },
        create: function (options) {
            return new TestReferenceViewModel(options.data);
        }
    };

    // Data
    self.referenceList = ko.mapping.fromJS([], referenceListMapping);
    self.referenceListValid = ko.observable(false);

    self.validationId = ko.observable(false);

    self.selectedReference = ko.observable(false);
    self.selectedSession = ko.observable(false);

    self.fetching = ko.observable(false);

    // Derived data
    self.sessionList = ko.pureComputed(function () { return resultsViewModel.sessionList() });
    self.sessionListValid = ko.pureComputed(function () { return resultsViewModel.sessionList() });

    self.sessionListNamed = ko.computed(function () {
        return ko.utils.arrayFilter(resultsViewModel.sessionList(), function (item) {
            return item.name() != "";
        });
    });

    self.selectionMade = ko.pureComputed(function () {
        return undefined !== self.selectedReference() &&
               undefined !== self.selectedSession();
    });

    // Behaviours
    self.updateReferenceList = function (fnCallback)
    {
        if (appViewModel.endpoints.references)
        {
            self.fetching(true);
            $.get(appViewModel.endpoints.references, function (data)
            {
                ko.mapping.fromJS(data.references, self.referenceList);
                self.referenceListValid(true);
                if (fnCallback) {
                    fnCallback();
                }
                self.fetching(false);
            }, 'json');
        }
    };

    self.startValidation = function()
    {
        if (appViewModel.endpoints.reports)
        {
            self.fetching(true);
            $.post(appViewModel.endpoints.reports, {
                session: self.selectedSession(),
                reference: self.selectedReference()
            }, function (data)
            {
                self.fetching(false);
            }, 'json');
        }
    }

    appViewModel.isValidation.subscribe(function (selected)
    {
        if (selected) {
            resultsViewModel.updateSessionList();
            self.updateReferenceList();
        }
        else {

        }
    });
}