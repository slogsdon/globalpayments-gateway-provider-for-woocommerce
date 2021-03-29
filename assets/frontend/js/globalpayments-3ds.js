this.GlobalPayments = this.GlobalPayments || {};
this.GlobalPayments.ThreeDSecure = (function (exports) {
    'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    /* global Reflect, Promise */

    var extendStatics = function(d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };

    function __extends(d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }

    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }

    function __generator(thisArg, body) {
        var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
        return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
        function verb(n) { return function (v) { return step([n, v]); }; }
        function step(op) {
            if (f) throw new TypeError("Generator is already executing.");
            while (_) try {
                if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
                if (y = 0, t) op = [op[0] & 2, t.value];
                switch (op[0]) {
                    case 0: case 1: t = op; break;
                    case 4: _.label++; return { value: op[1], done: false };
                    case 5: _.label++; y = op[1]; op = [0]; continue;
                    case 7: op = _.ops.pop(); _.trys.pop(); continue;
                    default:
                        if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                        if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                        if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                        if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                        if (t[2]) _.ops.pop();
                        _.trys.pop(); continue;
                }
                op = body.call(thisArg, _);
            } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
            if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
        }
    }

    (function (AuthenticationSource) {
        AuthenticationSource["Browser"] = "BROWSER";
        AuthenticationSource["MobileSDK"] = "MOBILE_SDK";
        AuthenticationSource["StoredRecurring"] = "STORED_RECURRING";
    })(exports.AuthenticationSource || (exports.AuthenticationSource = {}));
    (function (AuthenticationRequestType) {
        AuthenticationRequestType["AddCard"] = "ADD_CARD";
        AuthenticationRequestType["CardholderVerification"] = "CARDHOLDER_VERIFICATION";
        AuthenticationRequestType["InstalmentTransaction"] = "INSTALMENT_TRANSACTION";
        AuthenticationRequestType["MaintainCard"] = "MAINTAIN_CARD";
        AuthenticationRequestType["PaymentTransaction"] = "PAYMENT_TRANSACTION";
        AuthenticationRequestType["RecurringTransaction"] = "RECURRING_TRANSACTION";
    })(exports.AuthenticationRequestType || (exports.AuthenticationRequestType = {}));
    (function (ChallengeRequestIndicator) {
        ChallengeRequestIndicator["ChallengeMandated"] = "CHALLENGE_MANDATED";
        ChallengeRequestIndicator["ChallengePreferred"] = "CHALLENGE_PREFERRED";
        ChallengeRequestIndicator["NoChallengeRequested"] = "NO_CHALLENGE_REQUESTED";
        ChallengeRequestIndicator["NoPreference"] = "NO_PREFERENCE";
    })(exports.ChallengeRequestIndicator || (exports.ChallengeRequestIndicator = {}));
    (function (ChallengeWindowSize) {
        ChallengeWindowSize["FullScreen"] = "FULL_SCREEN";
        ChallengeWindowSize["Windowed250x400"] = "WINDOWED_250X400";
        ChallengeWindowSize["Windowed390x400"] = "WINDOWED_390X400";
        ChallengeWindowSize["Windowed500x600"] = "WINDOWED_500X600";
        ChallengeWindowSize["Windowed600x400"] = "WINDOWED_600X400";
    })(exports.ChallengeWindowSize || (exports.ChallengeWindowSize = {}));
    (function (MessageCategory) {
        MessageCategory["NonPayment"] = "NON_PAYMENT_AUTHENTICATION";
        MessageCategory["Payment"] = "PAYMENT_AUTHENTICATION";
    })(exports.MessageCategory || (exports.MessageCategory = {}));
    (function (MethodUrlCompletion) {
        MethodUrlCompletion["Unavailable"] = "UNAVAILABLE";
        MethodUrlCompletion["No"] = "NO";
        MethodUrlCompletion["Yes"] = "YES";
    })(exports.MethodUrlCompletion || (exports.MethodUrlCompletion = {}));
    (function (TransactionStatus) {
        TransactionStatus["AuthenticationAttemptedButNotSuccessful"] = "AUTHENTICATION_ATTEMPTED_BUT_NOT_SUCCESSFUL";
        TransactionStatus["AuthenticationCouldNotBePerformed"] = "AUTHENTICATION_COULD_NOT_BE_PERFORMED";
        TransactionStatus["AuthenticationFailed"] = "AUTHENTICATION_FAILED";
        TransactionStatus["AuthenticationIssuerRejected"] = "AUTHENTICATION_ISSUER_REJECTED";
        TransactionStatus["AuthenticationSuccessful"] = "AUTHENTICATION_SUCCESSFUL";
        TransactionStatus["ChallengeRequired"] = "CHALLENGE_REQUIRED";
    })(exports.TransactionStatus || (exports.TransactionStatus = {}));
    (function (TransactionStatusReason) {
        TransactionStatusReason["CardAuthenticationFailed"] = "CARD_AUTHENTICATION_FAILED";
        TransactionStatusReason["UnknownDevice"] = "UNKNOWN_DEVICE";
        TransactionStatusReason["UnsupportedDevice"] = "UNSUPPORTED_DEVICE";
        TransactionStatusReason["ExceedsAuthenticationFrequencyLimit"] = "EXCEEDS_AUTHENTICATION_FREQUENCY_LIMIT";
        TransactionStatusReason["ExpiredCard"] = "EXPIRED_CARD";
        TransactionStatusReason["InvalidCardNumber"] = "INVALID_CARD_NUMBER";
        TransactionStatusReason["InvalidTransaction"] = "INVALID_TRANSACTION";
        TransactionStatusReason["NoCardRecord"] = "NO_CARD_RECORD";
        TransactionStatusReason["SecurityFailure"] = "SECURITY_FAILURE";
        TransactionStatusReason["StolenCard"] = "STOLEN_CARD";
        TransactionStatusReason["SuspectedFraud"] = "SUSPECTED_FRAUD";
        TransactionStatusReason["TransactionNotPermittedToCardholder"] = "TRANSACTION_NOT_PERMITTED_TO_CARDHOLDER";
        TransactionStatusReason["CardholderNotEnrolledInService"] = "CARDHOLDER_NOT_ENROLLED_IN_SERVICE";
        TransactionStatusReason["TransactionTimedOutAtTheAcs"] = "TRANSACTION_TIMED_OUT_AT_THE_ACS";
        TransactionStatusReason["LowConfidence"] = "LOW_CONFIDENCE";
        TransactionStatusReason["MediumConfidence"] = "MEDIUM_CONFIDENCE";
        TransactionStatusReason["HighConfidence"] = "HIGH_CONFIDENCE";
        TransactionStatusReason["VeryHighConfidence"] = "VERY_HIGH_CONFIDENCE";
        TransactionStatusReason["ExceedsAcsMaximumChallenges"] = "EXCEEDS_ACS_MAXIMUM_CHALLENGES";
        TransactionStatusReason["NonPaymentTransactionNotSupported"] = "NON_PAYMENT_TRANSACTION_NOT_SUPPORTED";
        TransactionStatusReason["ThreeriTransactionNotSupported"] = "THREERI_TRANSACTION_NOT_SUPPORTED";
    })(exports.TransactionStatusReason || (exports.TransactionStatusReason = {}));
    function colorDepth(value) {
        var result = "";
        switch (value) {
            case 1:
                return "ONE_BIT";
            case 2:
                result += "TWO";
                break;
            case 4:
                result += "FOUR";
                break;
            case 8:
                result += "EIGHT";
                break;
            case 15:
                result += "FIFTEEN";
                break;
            case 16:
                result += "SIXTEEN";
                break;
            case 24:
            case 30:
                result += "TWENTY_FOUR";
                break;
            case 32:
                result += "THIRTY_TWO";
                break;
            case 48:
                result += "FORTY_EIGHT";
                break;
            default:
                throw new Error("Unknown color depth '" + value + "'");
        }
        return result + "_BITS";
    }
    function dimensionsFromChallengeWindowSize(options) {
        var height = 0;
        var width = 0;
        switch (options.size || options.windowSize) {
            case exports.ChallengeWindowSize.Windowed250x400:
                height = 250;
                width = 400;
                break;
            case exports.ChallengeWindowSize.Windowed390x400:
                height = 390;
                width = 400;
                break;
            case exports.ChallengeWindowSize.Windowed500x600:
                height = 500;
                width = 600;
                break;
            case exports.ChallengeWindowSize.Windowed600x400:
                height = 600;
                width = 400;
                break;
        }
        return { height: height, width: width };
    }
    function messageCategoryFromAuthenticationRequestType(authenticationRequestType) {
        switch (authenticationRequestType) {
            case exports.AuthenticationRequestType.AddCard:
            case exports.AuthenticationRequestType.CardholderVerification:
            case exports.AuthenticationRequestType.MaintainCard:
                return exports.MessageCategory.NonPayment;
            case exports.AuthenticationRequestType.InstalmentTransaction:
            case exports.AuthenticationRequestType.PaymentTransaction:
            case exports.AuthenticationRequestType.RecurringTransaction:
            default:
                return exports.MessageCategory.Payment;
        }
    }

    var GPError = /** @class */ (function (_super) {
        __extends(GPError, _super);
        function GPError(reasons, message) {
            var _this = _super.call(this, message || "Error: see `reasons` property") || this;
            _this.error = true;
            _this.reasons = reasons;
            return _this;
        }
        return GPError;
    }(Error));

    function handleNotificationMessageEvent(event, data, origin) {
        if (window.parent !== window) {
            window.parent.postMessage({ data: data, event: event }, origin || window.location.origin);
        }
    }

    function makeRequest(endpoint, data) {
        return __awaiter(this, void 0, void 0, function () {
            var headers, rawResponse, _a, e_1, reasons;
            var _b;
            return __generator(this, function (_c) {
                switch (_c.label) {
                    case 0:
                        headers = {
                            "Content-Type": "application/json",
                        };
                        _c.label = 1;
                    case 1:
                        _c.trys.push([1, 6, , 7]);
                        return [4 /*yield*/, fetch(endpoint, {
                                body: JSON.stringify(data),
                                credentials: "omit",
                                headers: typeof Headers !== "undefined" ? new Headers(headers) : headers,
                                method: "POST",
                            })];
                    case 2:
                        rawResponse = _c.sent();
                        if (!!rawResponse.ok) return [3 /*break*/, 4];
                        _a = GPError.bind;
                        _b = {
                            code: rawResponse.status.toString()
                        };
                        return [4 /*yield*/, rawResponse.text()];
                    case 3: throw new (_a.apply(GPError, [void 0, [
                            (_b.message = _c.sent(),
                                _b)
                        ], rawResponse.statusText]))();
                    case 4: return [4 /*yield*/, rawResponse.json()];
                    case 5: return [2 /*return*/, _c.sent()];
                    case 6:
                        e_1 = _c.sent();
                        reasons = [{ code: e_1.name, message: e_1.message }];
                        if (e_1.reasons) {
                            reasons = reasons.concat(e_1.reasons);
                        }
                        throw new GPError(reasons);
                    case 7: return [2 /*return*/];
                }
            });
        });
    }

    // most of this module is pulled from the legacy Realex Payments JavaScript library
    var isWindowsMobileOs = /Windows Phone|IEMobile/.test(navigator.userAgent);
    var isAndroidOrIOs = /Android|iPad|iPhone|iPod/.test(navigator.userAgent);
    var isMobileXS = ((window.innerWidth > 0 ? window.innerWidth : screen.width) <= 360
        ? true
        : false) ||
        ((window.innerHeight > 0 ? window.innerHeight : screen.height) <= 360
            ? true
            : false);
    // For IOs/Android and small screen devices always open in new tab/window
    // TODO: Confirm/implement once sandbox support is in place
    var isMobileNewTab = !isWindowsMobileOs && (isAndroidOrIOs || isMobileXS);
    // Display IFrame on WIndows Phone OS mobile devices
    var isMobileIFrame = isWindowsMobileOs || isMobileNewTab;
    var randomId = Math.random()
        .toString(16)
        .substr(2, 8);
    function createLightbox(iFrame, options) {
        // Create the overlay
        var overlayElement = createOverlay();
        // Create the spinner
        var spinner = createSpinner();
        document.body.appendChild(spinner);
        var _a = dimensionsFromChallengeWindowSize(options), height = _a.height, width = _a.width;
        // Configure the iframe
        if (height) {
            iFrame.setAttribute("height", height + "px");
        }
        iFrame.setAttribute("frameBorder", "0");
        if (width) {
            iFrame.setAttribute("width", width + "px");
        }
        iFrame.setAttribute("seamless", "seamless");
        iFrame.style.zIndex = "10001";
        iFrame.style.position = "absolute";
        iFrame.style.transition = "transform 0.5s ease-in-out";
        iFrame.style.transform = "scale(0.7)";
        iFrame.style.opacity = "0";
        overlayElement.appendChild(iFrame);
        if (isMobileIFrame || options.windowSize === exports.ChallengeWindowSize.FullScreen) {
            iFrame.style.top = "0px";
            iFrame.style.bottom = "0px";
            iFrame.style.left = "0px";
            iFrame.style.marginLeft = "0px;";
            iFrame.style.width = "100%";
            iFrame.style.height = "100%";
            iFrame.style.minHeight = "100%";
            iFrame.style.WebkitTransform = "translate3d(0,0,0)";
            iFrame.style.transform = "translate3d(0, 0, 0)";
            var metaTag = document.createElement("meta");
            metaTag.name = "viewport";
            metaTag.content =
                "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0";
            document.getElementsByTagName("head")[0].appendChild(metaTag);
        }
        else {
            iFrame.style.top = "40px";
            iFrame.style.left = "50%";
            iFrame.style.marginLeft = "-" + width / 2 + "px";
        }
        iFrame.onload = getIFrameOnloadEventHandler(iFrame, spinner, overlayElement, options);
    }
    function closeModal() {
        Array.prototype.slice.call(document
            .querySelectorAll("[target$=\"-" + randomId + "\"],[id$=\"-" + randomId + "\"]"))
            .forEach(function (element) {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        });
    }
    function createOverlay() {
        var overlay = document.createElement("div");
        overlay.setAttribute("id", "GlobalPayments-overlay-" + randomId);
        overlay.style.position = "fixed";
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.top = "0";
        overlay.style.left = "0";
        overlay.style.transition = "all 0.3s ease-in-out";
        overlay.style.zIndex = "100";
        if (isMobileIFrame) {
            overlay.style.position = "absolute !important";
            overlay.style.WebkitOverflowScrolling = "touch";
            overlay.style.overflowX = "hidden";
            overlay.style.overflowY = "scroll";
        }
        document.body.appendChild(overlay);
        setTimeout(function () {
            overlay.style.background = "rgba(0, 0, 0, 0.7)";
        }, 1);
        return overlay;
    }
    function createCloseButton(options) {
        if (document.getElementById("GlobalPayments-frame-close-" + randomId) !== null) {
            return;
        }
        var closeButton = document.createElement("img");
        closeButton.id = "GlobalPayments-frame-close-" + randomId;
        closeButton.src =
            // tslint:disable-next-line:max-line-length
            "data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAYAAAA7bUf6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QUJFRjU1MEIzMUQ3MTFFNThGQjNERjg2NEZCRjFDOTUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QUJFRjU1MEMzMUQ3MTFFNThGQjNERjg2NEZCRjFDOTUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpBQkVGNTUwOTMxRDcxMUU1OEZCM0RGODY0RkJGMUM5NSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpBQkVGNTUwQTMxRDcxMUU1OEZCM0RGODY0RkJGMUM5NSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PlHco5QAAAHpSURBVHjafFRdTsJAEF42JaTKn4glGIg++qgX4AAchHAJkiZcwnAQD8AF4NFHCaC2VgWkIQQsfl/jNJUik8Duzs/XmW9mN7Xb7VRc5vP5zWKxaK5Wq8Zmu72FqobfJG0YQ9M0+/l8/qFQKDzGY1JxENd1288vLy1s786KRZXJZCLber1Wn7MZt4PLarVnWdZ9AmQ8Hncc17UvymVdBMB/MgPQm+cFFcuy6/V6lzqDf57ntWGwYdBIVx0TfkBD6I9M35iRJgfIoAVjBLDZbA4CiJ5+9AdQi/EahibqDTkQx6fRSIHcPwA8Uy9A9Gcc47Xv+w2wzhRDYzqdVihLIbsIiCvP1NNOoX/29FQx3vgOgtt4FyRdCgPRarX4+goB9vkyAMh443cOEsIAAcjncuoI4TXWMAmCIGFhCQLAdZ8jym/cRJ+Y5nC5XCYAhINKpZLgSISZgoqh5iiLQrojAFICVwGS7tCfe5DbZzkP56XS4NVxwvTI/vXVVYIDnqmnnX70ZxzjNS8THHooK5hMpxHQIREA+tEfA9djfHR3MHkdx3Hspe9r3B+VzWaj2RESyR2mlCUE4MoGQDdxiwHURq2t94+PO9bMIYyTyDNLwMoM7g8+BfKeYGniyw2MdfSehF3Qmk1IvCc/AgwAaS86Etp38bUAAAAASUVORK5CYII=";
        closeButton.style.transition = "all 0.5s ease-in-out";
        closeButton.style.opacity = "0";
        closeButton.style.float = "left";
        closeButton.style.position = "absolute";
        closeButton.style.left = "50%";
        closeButton.style.zIndex = "99999999";
        closeButton.style.top = "30px";
        var width = dimensionsFromChallengeWindowSize(options).width;
        if (width) {
            closeButton.style.marginLeft = width / 2 - 8 /* half image width */ + "px";
        }
        setTimeout(function () {
            closeButton.style.opacity = "1";
        }, 500);
        if (isMobileIFrame || options.windowSize === exports.ChallengeWindowSize.FullScreen) {
            closeButton.style.float = "right";
            closeButton.style.top = "20px";
            closeButton.style.left = "initial";
            closeButton.style.marginLeft = "0px";
            closeButton.style.right = "20px";
        }
        return closeButton;
    }
    function createSpinner() {
        var spinner = document.createElement("img");
        spinner.setAttribute("src", 
        // tslint:disable-next-line:max-line-length
        "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjAiIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjMycHgiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiB4bWw6c3BhY2U9InByZXNlcnZlIj48Zz48cGF0aCBkPSJNMzguNTIgMzMuMzdMMjEuMzYgMTYuMkE2My42IDYzLjYgMCAwIDEgNTkuNS4xNnYyNC4zYTM5LjUgMzkuNSAwIDAgMC0yMC45OCA4LjkyeiIgZmlsbD0iIzAwNzBiYSIgZmlsbC1vcGFjaXR5PSIxIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoNDUgNjQgNjQpIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoOTAgNjQgNjQpIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoMTM1IDY0IDY0KSIvPjxwYXRoIGQ9Ik0zOC41MiAzMy4zN0wyMS4zNiAxNi4yQTYzLjYgNjMuNiAwIDAgMSA1OS41LjE2djI0LjNhMzkuNSAzOS41IDAgMCAwLTIwLjk4IDguOTJ6IiBmaWxsPSIjYzBkY2VlIiBmaWxsLW9wYWNpdHk9IjAuMjUiIHRyYW5zZm9ybT0icm90YXRlKDE4MCA2NCA2NCkiLz48cGF0aCBkPSJNMzguNTIgMzMuMzdMMjEuMzYgMTYuMkE2My42IDYzLjYgMCAwIDEgNTkuNS4xNnYyNC4zYTM5LjUgMzkuNSAwIDAgMC0yMC45OCA4LjkyeiIgZmlsbD0iI2MwZGNlZSIgZmlsbC1vcGFjaXR5PSIwLjI1IiB0cmFuc2Zvcm09InJvdGF0ZSgyMjUgNjQgNjQpIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoMjcwIDY0IDY0KSIvPjxwYXRoIGQ9Ik0zOC41MiAzMy4zN0wyMS4zNiAxNi4yQTYzLjYgNjMuNiAwIDAgMSA1OS41LjE2djI0LjNhMzkuNSAzOS41IDAgMCAwLTIwLjk4IDguOTJ6IiBmaWxsPSIjYzBkY2VlIiBmaWxsLW9wYWNpdHk9IjAuMjUiIHRyYW5zZm9ybT0icm90YXRlKDMxNSA2NCA2NCkiLz48YW5pbWF0ZVRyYW5zZm9ybSBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InJvdGF0ZSIgdmFsdWVzPSIwIDY0IDY0OzQ1IDY0IDY0OzkwIDY0IDY0OzEzNSA2NCA2NDsxODAgNjQgNjQ7MjI1IDY0IDY0OzI3MCA2NCA2NDszMTUgNjQgNjQiIGNhbGNNb2RlPSJkaXNjcmV0ZSIgZHVyPSIxMjgwbXMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIj48L2FuaW1hdGVUcmFuc2Zvcm0+PC9nPjwvc3ZnPg==");
        spinner.setAttribute("id", "GlobalPayments-loader-" + randomId);
        spinner.style.left = "50%";
        spinner.style.position = "fixed";
        spinner.style.background = "#FFFFFF";
        spinner.style.borderRadius = "50%";
        spinner.style.width = "30px";
        spinner.style.zIndex = "200";
        spinner.style.marginLeft = "-15px";
        spinner.style.top = "120px";
        return spinner;
    }
    function getIFrameOnloadEventHandler(iFrame, spinner, overlayElement, options) {
        return function () {
            iFrame.style.opacity = "1";
            iFrame.style.transform = "scale(1)";
            iFrame.style.backgroundColor = "#ffffff";
            if (spinner.parentNode) {
                spinner.parentNode.removeChild(spinner);
            }
            var closeButton;
            closeButton = createCloseButton(options);
            if (closeButton) {
                overlayElement.appendChild(closeButton);
                closeButton.addEventListener("click", function () {
                    if (closeButton) {
                        closeModal();
                    }
                }, true);
            }
        };
    }

    function postToIframe(endpoint, fields, options) {
        return new Promise(function (resolve, reject) {
            var timeout;
            if (options.timeout) {
                timeout = setTimeout(function () {
                    ensureIframeClosed(timeout);
                    reject(new Error("timeout reached"));
                }, options.timeout);
            }
            var iframe = document.createElement("iframe");
            iframe.id = iframe.name = "GlobalPayments-3DSecure-" + randomId;
            iframe.style.display = options.hide ? "none" : "inherit";
            var form = createForm(endpoint, iframe.id, fields);
            switch (options.displayMode) {
                case "redirect":
                    // TODO: Add redirect support once sandbox environment respects configured
                    // challengeNotificationUrl instead of hardcoded value
                    ensureIframeClosed(timeout);
                    reject(new Error("Not implemented"));
                    return;
                case "lightbox":
                    createLightbox(iframe, options);
                    break;
                case "embedded":
                default:
                    if (!handleEmbeddedIframe(reject, { iframe: iframe, timeout: timeout }, options)) {
                        // rejected
                        return;
                    }
                    break;
            }
            window.addEventListener("message", getWindowMessageEventHandler(resolve, {
                origin: options.origin,
                timeout: timeout,
            }));
            document.body.appendChild(form);
            form.submit();
        });
    }
    function createForm(action, target, fields) {
        var form = document.createElement("form");
        form.setAttribute("method", "POST");
        form.setAttribute("action", action);
        form.setAttribute("target", target);
        for (var _i = 0, fields_1 = fields; _i < fields_1.length; _i++) {
            var field = fields_1[_i];
            var input = document.createElement("input");
            input.setAttribute("type", "hidden");
            input.setAttribute("name", field.name);
            input.setAttribute("value", field.value);
            form.appendChild(input);
        }
        return form;
    }
    function ensureIframeClosed(timeout) {
        if (timeout) {
            clearTimeout(timeout);
        }
        try {
            Array.prototype.slice.call(document
                .querySelectorAll("[target$=\"-" + randomId + "\"],[id$=\"-" + randomId + "\"]"))
                .forEach(function (element) {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });
        }
        catch (e) {
            /** */
        }
    }
    function getWindowMessageEventHandler(resolve, data) {
        return function (e) {
            var origin = data.origin || window.location.origin;
            if (origin !== e.origin) {
                return;
            }
            ensureIframeClosed(data.timeout || 0);
            resolve(e.data);
        };
    }
    function handleEmbeddedIframe(reject, data, options) {
        var targetElement;
        if (options.hide) {
            targetElement = document.body;
        }
        else if (typeof options.target === "string") {
            targetElement = document.querySelector(options.target);
        }
        else {
            targetElement = options.target;
        }
        if (!targetElement) {
            ensureIframeClosed(data.timeout || 0);
            reject(new Error("Embed target not found"));
            return false;
        }
        var _a = dimensionsFromChallengeWindowSize(options), height = _a.height, width = _a.width;
        if (data.iframe) {
            data.iframe.setAttribute("height", height ? height + "px" : "100%");
            data.iframe.setAttribute("width", width ? width + "px" : "100%");
            targetElement.appendChild(data.iframe);
        }
        return true;
    }

    /**
     * Retrieves client browser runtime data.
     */
    function getBrowserData() {
        var now = new Date();
        return {
            colorDepth: screen && colorDepth(screen.colorDepth),
            javaEnabled: navigator && navigator.javaEnabled(),
            javascriptEnabled: true,
            language: navigator && navigator.language,
            screenHeight: screen && screen.height,
            screenWidth: screen && screen.width,
            time: now,
            timezoneOffset: now.getTimezoneOffset() / 60,
            userAgent: navigator && navigator.userAgent,
        };
    }
    /**
     * Facilitates backend request to merchant integration to check the enrolled 3DS version for the consumer's card.
     *
     * @param endpoint Merchant integration endpoint responsible for performing the version check
     * @param data Request data to aid in version check request
     * @throws When an error occurred during the request
     */
    function checkVersion(endpoint, data) {
        return __awaiter(this, void 0, void 0, function () {
            var response, e_1, reasons;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        data = data || {};
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 4, , 5]);
                        return [4 /*yield*/, makeRequest(endpoint, data)];
                    case 2:
                        response = (_a.sent());
                        return [4 /*yield*/, handle3dsVersionCheck(response, data.methodWindow)];
                    case 3: return [2 /*return*/, _a.sent()];
                    case 4:
                        e_1 = _a.sent();
                        reasons = [{ code: e_1.name, message: e_1.message }];
                        if (e_1.reasons) {
                            reasons = reasons.concat(e_1.reasons);
                        }
                        throw new GPError(reasons);
                    case 5: return [2 /*return*/];
                }
            });
        });
    }
    /**
     * Facilitates backend request to merchant integration to initiate 3DS 2.0 authentication workflows with the consumer.
     *
     * @param endpoint Merchant integration endpoint responsible for initiating the authentication request
     * @param data Request data to aid in initiating authentication
     * @throws When an error occurred during the request
     */
    function initiateAuthentication(endpoint, data) {
        return __awaiter(this, void 0, void 0, function () {
            var response, e_2, reasons;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 3, , 4]);
                        data.authenticationSource =
                            data.authenticationSource || exports.AuthenticationSource.Browser;
                        data.authenticationRequestType =
                            data.authenticationRequestType ||
                                exports.AuthenticationRequestType.PaymentTransaction;
                        data.messageCategory =
                            data.messageCategory ||
                                messageCategoryFromAuthenticationRequestType(data.authenticationRequestType);
                        data.challengeRequestIndicator =
                            data.challengeRequestIndicator || exports.ChallengeRequestIndicator.NoPreference;
                        // still needs ip address and accept header from server-side
                        data.browserData = data.browserData || getBrowserData();
                        return [4 /*yield*/, makeRequest(endpoint, data)];
                    case 1:
                        response = (_a.sent());
                        return [4 /*yield*/, handleInitiateAuthentication(response, data.challengeWindow)];
                    case 2: return [2 /*return*/, _a.sent()];
                    case 3:
                        e_2 = _a.sent();
                        reasons = [{ code: e_2.name, message: e_2.message }];
                        if (e_2.reasons) {
                            reasons = reasons.concat(e_2.reasons);
                        }
                        throw new GPError(reasons);
                    case 4: return [2 /*return*/];
                }
            });
        });
    }
    /**
     * Handles response from merchant integration endpoint for the version check request. When a card is enrolled and a
     * method URL is present, a hidden iframe to the method URL will be created to handle device fingerprinting
     * requirements.
     *
     * @param data Version check data from merchant integration endpoint
     * @param options Configuration options for the method window
     * @throws When a card is not enrolled
     */
    function handle3dsVersionCheck(data, options) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        if (!data.enrolled) {
                            throw new Error("Card not enrolled");
                        }
                        options = options || {};
                        options.hide = typeof options.hide === "undefined" ? true : options.hide;
                        options.timeout =
                            typeof options.timeout === "undefined" ? 30 * 1000 : options.timeout;
                        if (!data.methodUrl) return [3 /*break*/, 2];
                        return [4 /*yield*/, postToIframe(data.methodUrl, [{ name: "threeDSMethodData", value: data.methodData }], options)];
                    case 1:
                        _a.sent();
                        _a.label = 2;
                    case 2: return [2 /*return*/, data];
                }
            });
        });
    }
    /**
     * Handles response from merchant integration endpoint for initiating 3DS 2.0 authentication flows with consumer. If a
     * challenge is mandated, an iframe will be created for the issuer's necessary challenge URL.
     *
     * @param data Initiate authentication data from merchant integration endpoint
     * @param options Configuration options for the challenge window
     * @throws When a challenge is mandated but no challenge URL was supplied
     * @throws When an error occurred during the challenge request
     */
    function handleInitiateAuthentication(data, options) {
        return __awaiter(this, void 0, void 0, function () {
            var response;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        if (!data.challengeMandated) return [3 /*break*/, 2];
                        data.challenge = data.challenge || {};
                        if (!data.challenge.requestUrl) {
                            throw new Error("Invalid challenge state. Missing challenge URL");
                        }
                        return [4 /*yield*/, postToIframe(data.challenge.requestUrl, [
                                { name: "creq", value: data.challenge.encodedChallengeRequest },
                            ], options)];
                    case 1:
                        response = _a.sent();
                        data.challenge.response = response;
                        _a.label = 2;
                    case 2: return [2 /*return*/, data];
                }
            });
        });
    }
    /**
     * Assists with notifying the parent window of challenge status
     *
     * @param data Raw data from the challenge notification
     * @param origin Target origin for the message. Default is `window.location.origin`.
     */
    function handleChallengeNotification(data, origin) {
        handleNotificationMessageEvent("challengeNotification", data, origin);
    }
    /**
     * Assists with notifying the parent window of method status
     *
     * @param data Raw data from the method notification
     * @param origin Target origin for the message. Default is `window.location.origin`.
     */
    function handleMethodNotification(data, origin) {
        handleNotificationMessageEvent("methodNotification", data, origin);
    }

    exports.checkVersion = checkVersion;
    exports.colorDepth = colorDepth;
    exports.dimensionsFromChallengeWindowSize = dimensionsFromChallengeWindowSize;
    exports.getBrowserData = getBrowserData;
    exports.handle3dsVersionCheck = handle3dsVersionCheck;
    exports.handleChallengeNotification = handleChallengeNotification;
    exports.handleInitiateAuthentication = handleInitiateAuthentication;
    exports.handleMethodNotification = handleMethodNotification;
    exports.initiateAuthentication = initiateAuthentication;
    exports.messageCategoryFromAuthenticationRequestType = messageCategoryFromAuthenticationRequestType;

    Object.defineProperty(exports, '__esModule', { value: true });

    return exports;

}({}));
//# sourceMappingURL=globalpayments-3ds.js.map
