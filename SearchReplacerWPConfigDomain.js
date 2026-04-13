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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const path_1 = __importDefault(require("path"));
const fs_jetpack_1 = __importDefault(require("fs-jetpack"));
const escape_string_regexp_1 = __importDefault(require("escape-string-regexp"));
const url_parse_1 = __importDefault(require("url-parse"));
class SearchReplacerWPConfigDomain {
    constructor({ site, newDomain }) {
        this.site = site;
        this.newDomain = newDomain;
        this.wpConfigPath = path_1.default.join(site.paths.webRoot, 'wp-config.php');
    }
    escapeSingleQuote(string) {
        return string.replace(/'/, '\\\'');
    }
    phpConstantRegexFactory(constant) {
        return new RegExp(`define\\s*\\(\\s*['"](${(0, escape_string_regexp_1.default)(constant)})['"]\\s*,\\s*(.*?)\\s*\\)\\s*;`, 'g');
    }
    replaceDomainInURL(wpConfigContents, constant) {
        const matches = this.phpConstantRegexFactory(constant).exec(wpConfigContents);
        if (!matches || matches.length !== 3) {
            return wpConfigContents;
        }
        /* Remove quotes from constant value */
        const constantValue = matches[2]
            .replace(/^['"]*/, '')
            .replace(/['"]*$/, '');
        const parsedUrl = (0, url_parse_1.default)(constantValue);
        parsedUrl.set('host', this.newDomain);
        return wpConfigContents.replace(this.phpConstantRegexFactory(constant), `define( '${constant}', '${this.escapeSingleQuote(parsedUrl.toString())}' );`);
    }
    replaceConstant(wpConfigContents, constant) {
        return wpConfigContents.replace(this.phpConstantRegexFactory(constant), `define( '${constant}', '${this.escapeSingleQuote(this.newDomain)}' );`);
    }
    run() {
        return __awaiter(this, void 0, void 0, function* () {
            let wpConfigContents = fs_jetpack_1.default.read(this.wpConfigPath);
            /* Domain in URL replacements */
            for (const constant of ['WP_HOME', 'WP_SITEURL']) {
                wpConfigContents = this.replaceDomainInURL(wpConfigContents, constant);
            }
            /* Simple domain replacements */
            for (const constant of ['DOMAIN_CURRENT_SITE']) {
                wpConfigContents = this.replaceConstant(wpConfigContents, constant);
            }
            fs_jetpack_1.default.write(this.wpConfigPath, wpConfigContents);
            return wpConfigContents;
        });
    }
}
exports.default = SearchReplacerWPConfigDomain;
//# sourceMappingURL=SearchReplacerWPConfigDomain.js.map