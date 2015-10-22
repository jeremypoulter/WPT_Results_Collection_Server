/// <reference path="jquery-1.9.1.js" />
/// <reference path="bootstrap.js" />
/// <reference path="knockout-3.3.0.debug.js" />
/// <reference path="knockout.mapping-latest.debug.js" />
/// <reference path="sammy.js" />

function TestResultsViewModel(data)
{
    // Data
    var self = this;
    ko.mapping.fromJS(data, {}, self);

    // Deviced values
    self.result = ko.pureComputed(function () {
        switch (self.status())
        {
            case "OK":
                var sub = self.subtests();
                for (index in sub)
                {
                    var item = sub[index];
                    if ('PASS' != item.status()) {
                        return item.status();
                    }
                }
                return 'PASS';
            default:
                return self.status();
        }
    }, this);

    self.subResultsCount = function (type)
    {
        var count = 0;
        var sub = self.subtests();
        for (index in sub)
        {
            var item = sub[index];
            if (type == item.status()) {
                count++;
            }
        }
        return count;
    };
    self.subPass = ko.pureComputed(function () {
        return self.subResultsCount('PASS');
    }, this);
    self.subFail = ko.pureComputed(function () {
        return self.subResultsCount('FAIL');
    }, this);
    self.subTimeout = ko.pureComputed(function () {
        return self.subResultsCount('TIMEOUT');
    }, this);
    self.subError = ko.pureComputed(function () {
        return "ERROR" == self.status() ? 1 : 0;
    }, this);
    self.subCount = ko.pureComputed(function () {
        return self.subtests().length;
    }, this);

    self.resultClasses = ko.pureComputed(function () {
        return self.test.type() + " " + self.result().toLowerCase();
    }, this);
}

function HtmlTestToolViewModel()
{
    var sessionListMapping = {
        key: function(data) {
            return ko.utils.unwrapObservable(data.id);
        }
    };

    var resultsMapping = {
        create: function (options) {
            return new TestResultsViewModel(options.data);
        }
    };

    // Data
    var self = this;
    self.tab = ko.observable(null);
    self.session = ko.observable(false);
    self.sessionList = ko.mapping.fromJS([], sessionListMapping);
    self.sessionListValid = ko.observable(false);
    self.results = ko.mapping.fromJS([], resultsMapping);

    self.endpoints = [];

    // Derived data
    self.isHome = ko.pureComputed(function () {
        return this.tab() == 'home';
    }, this);
    self.isResults = ko.pureComputed(function () {
        return this.tab() == 'results';
    }, this);
    self.isAbout = ko.pureComputed(function () {
        return this.tab() == 'about';
    }, this);


    self.subtestsCount = function (fn) {
        var count = 0;
        var sub = self.results();
        for (index in sub) {
            var item = sub[index];
            count += fn(item);
        }
        return count;
    };

    self.totalPass = ko.pureComputed(function () {
        return self.subtestsCount(function (sub) { return sub.subPass(); });
    }, this);
    self.totalFail = ko.pureComputed(function () {
        return self.subtestsCount(function (sub) { return sub.subFail(); });
    }, this);
    self.totalTimeout = ko.pureComputed(function () {
        return self.subtestsCount(function (sub) { return sub.subTimeout(); });
    }, this);
    self.totalError = ko.pureComputed(function () {
        return self.subtestsCount(function (sub) { return sub.subError(); });
    }, this);
    self.totalCount = ko.pureComputed(function () {
        return self.subtestsCount(function (sub) { return sub.subCount(); });
    }, this);

    // Behaviours
    self.goToTab = function (tab) { location.hash = tab; };
    self.goToSession = function (session) { location.hash = 'results/' + session.id(); };
    self.updateSessionList = function (fnCallback) 
    {
        if(self.endpoints.results)
        {
            $.get(self.endpoints.results, function (data)
            {
                ko.mapping.fromJS(data.sessions, self.sessionList);
                self.sessionListValid(true);
                if (fnCallback) {
                    fnCallback();
                }
            }, 'json');
        }
    };

    self.updateResults = function (id)
    {
        if (self.endpoints.results)
        {
            if (self.sessionListValid())
            {
                $.get(self.getEndpointForSession(id), function (data) {
                    ko.mapping.fromJS(data.results, self.results);
                }, 'json');
            }
            else
            {
                self.updateSessionList(function () {
                    self.updateResults(id);
                })
            }
        }
    };

    self.getEndpointForSession = function (id) {
        for(index in self.sessionList())
        {
            var item = self.sessionList()[index];
            if (id == item.id()) {
                return item.href();
            }
        }
        return null;
    };

    self.session.subscribe(function (id) {
        if (false !== id) {
            location.hash = 'results/' + id;
            self.updateResults(id);
        } else {
            self.updateSessionList();
        }
    });

    self.tab.subscribe(function (newTab) 
    {
        switch(newTab)
        {
            case 'results':
                self.updateSessionList();
                break;
        }
    });

    // Client-side routes    
    var sammy = Sammy(function ()
    {
        this.get('#:tab', function () {
            self.tab(this.params.tab);
            self.session(false);
        });

        this.get('#results/:sessionId', function () {
            self.tab('results');
            self.session(this.params.sessionId);

        });
    });

    // Get the endpoints
    $.get("api.php", function (data) {
        data.links.forEach(function (item) {
            self.endpoints[item.rel] = item.href;
        });

        // The roughts depend on the endpoints being loaded so run them now
        sammy.run();
    }, 'json');
}

// Activates knockout.js
ko.applyBindings(new HtmlTestToolViewModel());

