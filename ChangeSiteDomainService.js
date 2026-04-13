"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const url_parse_1 = __importDefault(require("url-parse"));
const SearchReplacerWPConfigDomain_1 = __importDefault(require("./wp/SearchReplacerWPConfigDomain"));
const SearchReplacerWPDatabaseDomain_1 = __importDefault(require("./wp/SearchReplacerWPDatabaseDomain"));
const HooksMain_1 = __importDefault(require("../_helpers/HooksMain"));
const Site_1 = require("../../shared/models/Site");
const router_1 = __importDefault(require("../../shared/constants/router"));
class ChangeSiteDomainService {
    constructor(opts) {
        /**
         * This specific listener is used for the troubleshooting bar button to fix the home and siteurl options if they
         * do not match what Local expects based off of the routing mode.
         *
         * changeSiteDomainToHost() is used in other service and error handling is handled in said other services which is
         * why this method was created.
         */
        this.changeSiteDomainToHostIPCListener = (_, site) => __awaiter(this, void 0, void 0, function* () {
            if (!site) {
                // eslint-disable-next-line no-param-reassign
                site = this.getSelectedSite();
            }
            try {
                yield this.changeSiteDomainToHost(site);
            }
            catch (e) {
                if (site) {
                    this.sendIPCEvent('updateSiteStatus', site.id, 'running');
                }
                this.dialog.showErrorBox('Uh-oh! Unable to change site domain to host.', e.stack);
            }
        });
        this.ipcMain = opts.electron.ipcMain;
        this.dialog = opts.electron.dialog;
        this.siteData = opts.siteData;
        this.siteDatabase = opts.siteDatabase;
        this.wpCli = opts.wpCli;
        this.sendIPCEvent = opts.sendIPCEvent;
        this.appState = opts.appState;
        this.logger = opts.localLogger.child({
            thread: 'main',
            class: this.constructor.name,
        });
        this.domainChangeInFlightBySite = new Map();
        this.lastDomainChangeBySite = new Map();
    }
    listen() {
        this.ipcMain.on('changeSiteDomain', (event, site, domains, silent = false, updateDomain = true) => this.change(new Site_1.Site(site), domains, silent, updateDomain));
        this.ipcMain.on('changeSiteDomainToHost', this.changeSiteDomainToHostIPCListener);
    }
    getSelectedSite() {
        const { selectedSites, siteStatuses } = this.appState.getState();
        if (!selectedSites.length) {
            return null;
        }
        const siteId = selectedSites[0];
        const siteStatus = siteStatuses === null || siteStatuses === void 0 ? void 0 : siteStatuses[siteId];
        if (siteStatus !== 'running') {
            return null;
        }
        return Site_1.Site.find(siteId);
    }
    changeSiteDomainToHost(site) {
        return __awaiter(this, void 0, void 0, function* () {
            if (!site) {
                // eslint-disable-next-line no-param-reassign
                site = this.getSelectedSite();
            }
            if (!site) {
                return;
            }
            this.sendIPCEvent('updateSiteMessage', site.id, 'Changing Site Domain');
            const desiredProtocol = global.localhostRouting ? 'http' : undefined;
            const tablePrefix = yield this.siteDatabase.getTablePrefix(site);
            const home = yield this.siteDatabase.runQuery(site, `SELECT option_value FROM ${tablePrefix}options WHERE option_name='home' LIMIT 1`);
            if (typeof home !== 'string') {
                throw new Error('Unable to get \'home\' option from WordPress.');
            }
            const parsedHomeUrl = (0, url_parse_1.default)(home);
            if (!parsedHomeUrl.protocol || !parsedHomeUrl.host) {
                throw new Error('WP-CLI is not returning a valid \'home\' option from WordPress. '
                    + 'Please verify that there are not any PHP errors on the site.');
            }
            const homeHost = home
                .replace(/^https?:\/\//, '') // Remove protocol (http or https).
                .replace(/\/$/, ''); // Remove trailing slash, site.host will never have it.
            const siteurl = yield this.siteDatabase.runQuery(site, `SELECT option_value FROM ${tablePrefix}options WHERE option_name='siteurl' LIMIT 1`);
            if (typeof siteurl !== 'string') {
                throw new Error('Unable to get \'siteurl\' option from WordPress.');
            }
            const parsedSiteurl = (0, url_parse_1.default)(siteurl);
            if (!parsedSiteurl.protocol || !parsedSiteurl.host) {
                throw new Error('WP-CLI is not returning a valid \'siteurl\' option from WordPress. '
                    + 'Please verify that there are not any PHP errors on the site.');
            }
            const siteurlHost = siteurl
                .replace(/^https?:\/\//, '') // Remove protocol (http or https).
                .replace(/\/$/, ''); // Remove trailing slash, site.host will never have it.
            let hostFileUpdateRequired = false;
            yield this.removeTrailingSlashFromHomeAndSiteurl({
                site,
                home,
                siteurl,
                tablePrefix,
                siteDatabase: this.siteDatabase,
            });
            if (homeHost !== site.host || this.requiresProtocolChange(home)) {
                yield this.change(site, {
                    old: homeHost,
                    new: site.host,
                }, true, false, desiredProtocol);
                hostFileUpdateRequired = true;
            }
            if (siteurlHost !== site.host || this.requiresProtocolChange(siteurl)) {
                yield this.change(site, {
                    old: siteurlHost,
                    new: site.host,
                }, true, false, desiredProtocol);
                hostFileUpdateRequired = true;
            }
            if (hostFileUpdateRequired) {
                this.sendIPCEvent(router_1.default.RESTART, true);
            }
            this.sendIPCEvent('updateSiteStatus', site.id, 'running');
            this.sendIPCEvent('refreshTroubleshootingBar');
        });
    }
    /**
     * If using localhost routing, enforce the URLs to use HTTP.
     *
     * @param url
     */
    requiresProtocolChange(url) {
        if (!global.localhostRouting) {
            return false;
        }
        return !/^http:/.exec(url);
    }
    /**
     * Remove trailing slash from siteurl and home in the options table.
     *
     */
    removeTrailingSlashFromHomeAndSiteurl(_a) {
        var { site, tablePrefix, siteDatabase } = _a, props = __rest(_a, ["site", "tablePrefix", "siteDatabase"]);
        return __awaiter(this, void 0, void 0, function* () {
            // eslint-disable-next-line
            const makeQuery = (newValue, optionName) => `UPDATE ${tablePrefix}options SET option_value = '${newValue}' WHERE option_name='${optionName}'`;
            ['home', 'siteurl'].forEach((optionName) => __awaiter(this, void 0, void 0, function* () {
                // Remove trailing slash from home in options table
                // because it will break URLs during the search and replace.
                if (props[optionName].slice(-1) === '/') {
                    // wp-cli won't let us update the home to not use a trailing slash, so we use raw SQL.
                    yield siteDatabase.runQuery(site, makeQuery(props[optionName].slice(0, -1), optionName));
                }
            }));
        });
    }
    normalizeDomainList(domain) {
        if (Array.isArray(domain)) {
            return domain;
        }
        if (typeof domain === 'string' && domain.length) {
            return [domain];
        }
        return [];
    }
    isErroneousReversal(siteId, oldDomains, nextNewDomain) {
        const previous = this.lastDomainChangeBySite.get(siteId);
        if (!previous) {
            return false;
        }
        const { oldDomains: previousOldDomains, newDomain: previousNewDomain } = previous;
        return oldDomains.includes(previousNewDomain) && previousOldDomains.includes(nextNewDomain);
    }
    /**
     * @todo make work ipcAsync
     *
     * @param site
     * @param domains
     * @param silent
     * @param updateSite
     * @param desiredProtocol All replacements will be switched to this protocol. Useful for forcing HTTPS or removing force HTTPS.
     */
    change(site, domains, silent = false, updateSite = true, desiredProtocol = null) {
        return __awaiter(this, void 0, void 0, function* () {
            const oldDomains = this.normalizeDomainList(domains.old);
            if (oldDomains.includes(domains.new) || site.domain === domains.new) {
                return;
            }
            if (this.domainChangeInFlightBySite.has(site.id)) {
                this.logger.warn('Skipping duplicate changeSiteDomain call while a domain change is already in progress', {
                    siteId: site.id,
                    oldDomain: domains.old,
                    newDomain: domains.new,
                });
                return;
            }
            if (this.isErroneousReversal(site.id, oldDomains, domains.new)) {
                this.logger.warn('Skipping likely erroneous reversal changeSiteDomain call', {
                    siteId: site.id,
                    oldDomain: domains.old,
                    newDomain: domains.new,
                });
                return;
            }
            this.domainChangeInFlightBySite.set(site.id, true);
            this.sendIPCEvent('updateSiteStatus', site.id, 'provisioning');
            let didChange = false;
            try {
                site.domain = domains.new;
                if (updateSite && !global.localhostRouting) {
                    this.siteData.updateSite(site.id, {
                        domain: site.domain,
                    });
                }
                this.sendIPCEvent(router_1.default.RESTART, !silent);
                const searchReplaceArgs = {
                    site,
                    oldDomain: domains.old,
                    newDomain: domains.new,
                };
                const searchReplacerConfig = new SearchReplacerWPConfigDomain_1.default(searchReplaceArgs);
                const searchReplacerDatabase = new SearchReplacerWPDatabaseDomain_1.default({
                    siteDatabase: this.siteDatabase,
                    wpCli: this.wpCli,
                }, Object.assign(Object.assign({}, searchReplaceArgs), { desiredProtocol }));
                yield Promise.all([
                    HooksMain_1.default.doActions('changeSiteDomain', site, domains.old, domains.new),
                    /**
                     * We need to run the search and replace on the database first.
                     * wp-cli commands will break if the wp-config is updated.
                     */
                    searchReplacerDatabase.run(),
                    searchReplacerConfig.run(),
                ]);
                didChange = true;
            }
            finally {
                this.domainChangeInFlightBySite.delete(site.id);
                if (didChange) {
                    this.lastDomainChangeBySite.set(site.id, {
                        oldDomains,
                        newDomain: domains.new,
                    });
                }
                if (!silent) {
                    this.sendIPCEvent('updateSiteStatus', site.id, 'running');
                    this.sendIPCEvent('refreshTroubleshootingBar');
                }
            }
        });
    }
}
exports.default = ChangeSiteDomainService;
//# sourceMappingURL=ChangeSiteDomainService.js.map
