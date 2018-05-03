require = function() {
  function e(t, n, r) {
    function s(o, u) {
      if (!n[o]) {
        if (!t[o]) {
          var a = "function" == typeof require && require;
          if (!u && a) return a(o, !0);
          if (i) return i(o, !0);
          var f = new Error("Cannot find module '" + o + "'");
          throw f.code = "MODULE_NOT_FOUND", f;
        }
        var l = n[o] = {
          exports: {}
        };
        t[o][0].call(l.exports, function(e) {
          var n = t[o][1][e];
          return s(n || e);
        }, l, l.exports, e, t, n, r);
      }
      return n[o].exports;
    }
    var i = "function" == typeof require && require;
    for (var o = 0; o < r.length; o++) s(r[o]);
    return s;
  }
  return e;
}()({
  ApiNet: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "741ebShC6pAMot1xwqAUMNC", "ApiNet");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var config_1 = require("../config");
    function queryStringfy(obj) {
      var keys = Object.keys(obj);
      var str = "";
      keys.forEach(function(k) {
        obj[k] && (str += "&" + k + "=" + obj[k]);
      });
      return str.slice(1);
    }
    function loadRequest(url, data, method) {
      void 0 === method && (method = "GET");
      var promise = new Promise(function(resolve, reject) {
        var isreturn = false;
        setTimeout(function() {
          if (!isreturn) {
            reject(new Error("网络连接已超时"));
            isreturn = true;
          }
        }, 1e4);
        var datastr = null;
        url.startsWith("http://") || url.startsWith("https://") || (url = config_1.default.HOST_URL + url);
        if (url != config_1.default.HOST_URL + "/user/passport/login" && config_1.default.userData) {
          var token = config_1.default.userData.token;
          data.token = token;
        }
        data && (datastr = queryStringfy(data));
        var client = cc.loader.getXMLHttpRequest();
        if ("GET" === method) datastr ? client.open(method, url + "?" + datastr, true) : client.open(method, url, true); else if ("POST" === method) {
          client.open(method, url, true);
          client.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        }
        client.responseType = "text";
        client.onreadystatechange = function(ev) {
          if (client.readyState < 4) return;
          if (isreturn) return;
          if (200 === client.status) {
            isreturn = true;
            resolve(JSON.parse(client.response));
          } else {
            isreturn = true;
            reject(new Error(client.statusText));
          }
        };
        client.onerror = function(ev) {
          if (isreturn) return;
          isreturn = true;
          reject(new Error("网络请求出错"));
        };
        var log = "发送http请求 请求方式:" + method + " 请求地址:" + url;
        if (datastr) {
          client.send(datastr);
          log += "?" + datastr;
        } else client.send();
        cc.log(log);
      });
      return promise;
    }
    exports.default = {
      get: function(url, data) {
        void 0 === data && (data = {});
        return loadRequest(url, data, "GET");
      },
      post: function(url, data) {
        void 0 === data && (data = {});
        return loadRequest(url, data, "POST");
      }
    };
    cc._RF.pop();
  }, {
    "../config": "config"
  } ],
  AppData: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "e7755f/IXRA/qqEpHfSbUnd", "AppData");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var GridType;
    (function(GridType) {
      GridType[GridType["SECTION_TITLE"] = 0] = "SECTION_TITLE";
      GridType[GridType["TYPE_RANG"] = 1] = "TYPE_RANG";
      GridType[GridType["TYPE_DAXIAO"] = 2] = "TYPE_DAXIAO";
      GridType[GridType["TYPE_DUYING"] = 3] = "TYPE_DUYING";
      GridType[GridType["TYPE_DANSHUANG"] = 4] = "TYPE_DANSHUANG";
      GridType[GridType["TYPE_BODAN"] = 5] = "TYPE_BODAN";
      GridType[GridType["TYPE_ZHUDUI"] = 6] = "TYPE_ZHUDUI";
      GridType[GridType["TYPE_KEDUI"] = 7] = "TYPE_KEDUI";
      GridType[GridType["TYPE_ZONG"] = 8] = "TYPE_ZONG";
    })(GridType = exports.GridType || (exports.GridType = {}));
    var AppData = {
      matches: [ {
        id: 0,
        name: "全部比赛",
        logo: "all"
      }, {
        id: 0,
        name: "热门比赛",
        logo: "hot"
      } ],
      showMenus: [],
      games: [],
      odds: [],
      rules: [ {
        name: "让球",
        logo: "section_normal_1",
        selected: false,
        logo_hover: "section_selected_1",
        type: GridType.TYPE_RANG
      }, {
        name: "独赢",
        logo: "section_normal_2",
        selected: false,
        logo_hover: "section_selected_2",
        type: GridType.TYPE_DUYING
      }, {
        name: "大/小",
        logo: "section_normal_3",
        selected: false,
        logo_hover: "section_selected_3",
        type: GridType.TYPE_DAXIAO
      }, {
        name: "单双",
        logo: "section_normal_4",
        selected: false,
        logo_hover: "section_selected_4",
        type: GridType.TYPE_DANSHUANG
      }, {
        name: "波 胆",
        logo: "section_normal_5",
        selected: false,
        logo_hover: "section_selected_5",
        type: GridType.TYPE_BODAN
      }, {
        name: "主队进球",
        logo: "section_normal_6",
        selected: false,
        logo_hover: "section_selected_6",
        type: GridType.TYPE_ZHUDUI
      }, {
        name: "客队进球",
        logo: "section_normal_7",
        selected: false,
        logo_hover: "section_selected_7",
        type: GridType.TYPE_KEDUI
      }, {
        name: "总进球",
        logo: "section_normal_8",
        selected: false,
        logo_hover: "section_selected_8",
        type: GridType.TYPE_ZONG
      } ],
      sound: {
        bg: null,
        open: null,
        close: null,
        btn: null,
        select: null,
        menu: null
      },
      enableSound: true,
      imagesCaches: new Map(),
      menuList: null
    };
    window["AppData"] = AppData;
    exports.default = AppData;
    cc._RF.pop();
  }, {} ],
  AppGame: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "96749CL/61AXYzXlYxw2KEn", "AppGame");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var MenuItemDelegate_1 = require("./menus/MenuItemDelegate");
    var GameItemModel_1 = require("./games/model/GameItemModel");
    var GameItemDelegate_1 = require("./games/GameItemDelegate");
    var ChooseItemModel_1 = require("./games/model/ChooseItemModel");
    var HelpAlert_1 = require("./alert/HelpAlert");
    var LogListAlert_1 = require("./alert/LogListAlert");
    var config_1 = require("./config");
    var Helper_1 = require("./utils/Helper");
    var ApiNet_1 = require("./utils/ApiNet");
    var DateUtils_1 = require("./utils/DateUtils");
    var SearchListAlert_1 = require("./alert/SearchListAlert");
    var AppData_1 = require("./AppData");
    var NodeCaches_1 = require("./NodeCaches");
    var TipsAlert_1 = require("./alert/TipsAlert");
    var ListView_1 = require("./widget/ListView");
    var RecycleView_1 = require("./widget/RecycleView");
    var SectionGridItem_1 = require("./games/model/SectionGridItem");
    var Vec2 = cc.Vec2;
    var SectionScrollDelegate_1 = require("./games/SectionScrollDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var AppGame = function(_super) {
      __extends(AppGame, _super);
      function AppGame() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.gunqiu = null;
        _this.gold = null;
        _this.menuList = null;
        _this.typesList = null;
        _this.gameList = null;
        _this.pageView = null;
        _this.typeContainer = null;
        _this.currentMenuItem = null;
        _this.games = [];
        _this.currentGameItem = null;
        _this.router = "main";
        _this.sectionList = null;
        _this.appCanvas = null;
        return _this;
      }
      AppGame_1 = AppGame;
      AppGame.prototype.onLoad = function() {
        Helper_1.playBackgroundSound();
      };
      AppGame.prototype.start = function() {
        cc.director.setClearColor(cc.hexToColor("#04142e"));
        this.gunqiu.node.active = false;
        this.setupMenus();
        this.setupEvent();
        this.setupView();
        this.startHeartBeat();
        this.handleBackPressed();
        AppData_1.default.menuList = this.menuList;
      };
      AppGame.prototype.handleBackPressed = function() {
        var _this = this;
        cc.sys.os == cc.sys.OS_ANDROID && cc.systemEvent.on(cc.SystemEvent.EventType.KEY_UP, function(event) {
          event.keyCode == cc.KEY.back && _this.onBackPressed();
        }, this);
      };
      AppGame.prototype.setupEvent = function() {
        var _this = this;
        Helper_1.loadHelp();
        var backButton = cc.find("Canvas/back");
        var helpButton = cc.find("Canvas/help");
        var listButton = cc.find("Canvas/touzhu");
        var zaopanButton = cc.find("Canvas/zaopan");
        var jinRiButton = cc.find("Canvas/jinripan");
        var searchButton = cc.find("Canvas/search");
        searchButton.on("click", function() {
          SearchListAlert_1.default.show(.3);
        });
        backButton.on("click", function() {
          if ("choose" == _this.router) {
            Helper_1.playBtnSound();
            _this.animationBackToMain(false);
          } else _this.onBackPressed();
        });
        zaopanButton.on("click", function() {
          Helper_1.playBtnSound();
          "choose" == _this.router && _this.animationBackToMain(true);
          NodeCaches_1.default.menusItemCaches.forEach(function(item) {
            var component = item.getComponent(MenuItemDelegate_1.default);
            component.item.selected = false;
            component.refresh();
          });
          setTimeout(function() {
            var now = new Date();
            var array = AppData_1.default.games.filter(function(game) {
              var date = new Date(1e3 * game.play_time);
              return !DateUtils_1.default.isSameDate(now, date);
            });
            _this.reloadGame(array);
          }, 300);
        });
        jinRiButton.on("click", function() {
          Helper_1.playBtnSound();
          "choose" == _this.router && _this.animationBackToMain(true);
          NodeCaches_1.default.menusItemCaches.forEach(function(item) {
            var component = item.getComponent(MenuItemDelegate_1.default);
            component.item.selected = false;
            component.refresh();
          });
          setTimeout(function() {
            var now = new Date();
            var array = AppData_1.default.games.filter(function(game) {
              var date = new Date(1e3 * game.play_time);
              return DateUtils_1.default.isSameDate(now, date);
            });
            console.log(array);
            _this.reloadGame(array);
          }, 300);
        });
        helpButton.on("click", function() {
          HelpAlert_1.default.show(.3);
        });
        listButton.on("click", function() {
          LogListAlert_1.default.show(.3);
        });
      };
      AppGame.prototype.onBackPressed = function() {
        var _this = this;
        TipsAlert_1.default.show(.3, "确定退出游戏吗？", "即将退出游戏，是否继续?", function() {
          _this.logout();
        }, "是", "否", true);
      };
      AppGame.prototype.setupMenus = function() {
        var _this = this;
        NodeCaches_1.default.typeMenuItemCaches.forEach(function(item) {
          item.parent = _this.typesList.node;
          var component = item.getComponent(MenuItemDelegate_1.default);
          component.item.action = function(item, component) {
            _this.sectionList.getComponent(SectionScrollDelegate_1.default).isClick = true;
            _this.scrollToOffset(component.scrollStartY);
          };
        });
        AppData_1.default.matches.map(function(item) {
          item["action"] = function(item) {
            _this.loadGameItem(item);
          };
        });
        NodeCaches_1.default.menusItemCaches.map(function(item) {
          item.parent = _this.menuList.node;
          item.action = function(item) {
            _this.loadGameItem(item);
          };
        });
        var menusItemCach = NodeCaches_1.default.menusItemCaches[0];
        var menuDelegate = menusItemCach.getComponent(MenuItemDelegate_1.default);
        menuDelegate.item.selected = true;
        menuDelegate.refresh();
        NodeCaches_1.default.lastMatchMenu = menuDelegate;
        setTimeout(function() {
          _this.loadGameItem(null);
        }, 500);
        NodeCaches_1.default.sectionItemCaches.forEach(function(item) {
          item.parent = _this.typeContainer;
        });
        window["appGame"] = this;
      };
      AppGame.prototype.setupTypeScrollView = function(data) {
        var dataList = [];
        var recycleView = cc.find("view/content", this.sectionList).getComponent(RecycleView_1.default);
        var offset = 0;
        var showMenus = [];
        for (var i = 0; i < AppData_1.default.rules.length; i++) {
          var typeItem = data[i];
          var odds = typeItem["odds"];
          var items = [];
          var section = AppData_1.default.rules[i];
          var menuItem = NodeCaches_1.default.typeMenuItemCaches[i];
          var component = menuItem.getComponent(MenuItemDelegate_1.default);
          if (odds.length > 0) {
            component.node.active = true;
            var sectionTitleItem = new SectionGridItem_1.default();
            sectionTitleItem.gridType = AppData_1.GridType.SECTION_TITLE;
            sectionTitleItem.section = section;
            dataList.push(sectionTitleItem);
            component.scrollStartY = offset;
            offset -= 102;
            showMenus.push(component);
            var oddsItems = [];
            for (var j = 0; j < odds.length; j++) {
              var odd = odds[j];
              var chooseItem = new ChooseItemModel_1.default();
              var gridItem = new SectionGridItem_1.default();
              gridItem.gridType = section.type;
              chooseItem.guest = odd["guest"];
              chooseItem.handicap = odd["handicap"];
              chooseItem.home = odd["home"];
              chooseItem.name = typeItem["alias"];
              chooseItem.same = odd["same"];
              chooseItem.over = odd["over"];
              chooseItem.under = odd["under"];
              chooseItem.value = odd["value"];
              chooseItem.label = odd["label"];
              chooseItem.sd_1 = odd["sd_1"];
              chooseItem.sd_2 = odd["sd_2"];
              chooseItem.type_name = odd["type_name"];
              chooseItem.arena_id = typeItem["arena_id"];
              chooseItem.target_key = odd["key"];
              chooseItem.target_item = odd["target_item"];
              chooseItem.match = this.currentGameItemDelegate.item.match;
              chooseItem.teams = this.currentGameItemDelegate.item.teams;
              chooseItem.type = i;
              gridItem.item = chooseItem;
              oddsItems.push(gridItem);
            }
            if (section.type == AppData_1.GridType.TYPE_BODAN || section.type == AppData_1.GridType.TYPE_ZHUDUI || section.type == AppData_1.GridType.TYPE_KEDUI || section.type == AppData_1.GridType.TYPE_ZONG) {
              var row = Math.ceil(oddsItems.length / 6);
              offset -= 102 * row;
              for (var j = 0; j < row; j++) {
                var sectionChildGrid = new SectionGridItem_1.default();
                sectionChildGrid.gridType = section.type;
                sectionChildGrid.children = Helper_1.pagination(j + 1, 6, oddsItems);
                dataList.push(sectionChildGrid);
              }
              component.scrollEndY = offset;
            } else {
              var row = Math.ceil(oddsItems.length / 3);
              offset -= 102 * row;
              for (var j = 0; j < row; j++) {
                var sectionChildGrid = new SectionGridItem_1.default();
                sectionChildGrid.gridType = section.type;
                sectionChildGrid.children = Helper_1.pagination(j + 1, 3, oddsItems);
                dataList.push(sectionChildGrid);
              }
              component.scrollEndY = offset;
            }
          } else component.node.active = false;
        }
        console.log(dataList);
        console.log(showMenus);
        AppData_1.default.showMenus = showMenus;
        recycleView.dataList = dataList;
        recycleView.reloadData();
        this.updateTypeScroll();
      };
      AppGame.prototype.updateTypeScroll = function() {
        var selectItem = null;
        for (var i = 0; i < NodeCaches_1.default.typeMenuItemCaches.length; i++) {
          var typeItem = NodeCaches_1.default.typeMenuItemCaches[i];
          var component_1 = typeItem.getComponent(MenuItemDelegate_1.default);
          component_1.item.selected = false;
          if (typeItem.active && null == selectItem) {
            component_1.item.selected = true;
            NodeCaches_1.default.lastSelectTypeMenu = component_1;
            selectItem = component_1;
          }
          component_1.refresh();
        }
        var component = this.sectionList.getComponent(SectionScrollDelegate_1.default);
        var scrollView = this.sectionList.getComponent(cc.ScrollView);
        var number = Math.floor(scrollView.getScrollOffset().y);
        console.log(number);
        component.typeUpArrow.active = number > 0;
        component.typeDownArrow.active = number < Math.floor(Math.abs(scrollView.getMaxScrollOffset().y));
      };
      AppGame.updateScrollIndex = function() {
        var canvas = cc.find("Canvas");
        var scrollView = cc.find("page/scrollView", canvas).getComponent(cc.ScrollView);
        var content = cc.find("page/scrollView/view/content", canvas);
        console.log(content.childrenCount);
        for (var i = 0; i < content.childrenCount; i++) ;
      };
      AppGame.prototype.stopGame = function() {
        jsb.reflection.callStaticMethod("org/cocos2dx/javascript/AppActivity", "endGame", "()V");
      };
      AppGame.prototype.requestHeartBeat = function() {
        var _this = this;
        ApiNet_1.default.post("/index/common/heartbeat", {}).then(function(res) {
          console.log(res);
          if (0 == res.code) {
            var money = res.data.money;
            _this.gold.string = _this.formatCurrency(parseFloat(money));
            var online = res.data.has_online;
            0 == online && _this.logout();
          } else TipsAlert_1.default.show(.3, "提示", "登录已失效，请重新运行游戏", function() {
            _this.stopGame();
          });
        }).catch(function(error) {
          console.log(error);
        });
      };
      AppGame.prototype.logout = function() {
        var _this = this;
        ApiNet_1.default.post("/user/passport/logout", {}).then(function(res) {
          0 == res.code && _this.stopGame();
        });
      };
      AppGame.prototype.requestNotice = function() {
        ApiNet_1.default.post("/index/common/heartbeat", {}).then(function(res) {
          console.log(res);
        }).catch(function(error) {
          console.log(error);
        });
      };
      AppGame.prototype.startHeartBeat = function() {
        this.schedule(this.requestHeartBeat, 3);
      };
      AppGame.prototype.stopHeartBeat = function() {
        this.unschedule(this.requestHeartBeat);
      };
      AppGame.prototype.scrollToOffset = function(offsetY) {
        console.log(offsetY);
        var scrollView = this.sectionList.getComponent(cc.ScrollView);
        var time = .3 * Math.ceil(Math.ceil(Math.abs(scrollView.getScrollOffset().y + offsetY)) / 216);
        scrollView.scrollToOffset(cc.p(0, 1 - offsetY), time);
      };
      AppGame.prototype.scrollToItem = function(name) {
        var canvas = cc.find("Canvas");
        var scrollView = cc.find("page/scrollView", canvas).getComponent(cc.ScrollView);
        var content = cc.find("page/scrollView/view/content", canvas);
        var node = cc.find(name, content);
        node && scrollView.scrollToOffset(cc.p(0, -node.y), .3);
      };
      AppGame.prototype.onDestroy = function() {
        this.stopHeartBeat();
      };
      AppGame.prototype.loadGameItem = function(item) {
        Helper_1.showLoading();
        if (null == item || "全部比赛" == item.name) {
          var match_ids = AppData_1.default.matches.map(function(match) {
            return match.id;
          }).join(",");
          this.requestGames(match_ids);
        } else if ("热门比赛" == item.name) {
          var array = AppData_1.default.games.filter(function(game) {
            return 3 == game.hot;
          });
          this.reloadGame(array);
        } else if ("推荐比赛" == item.name) {
          var array = AppData_1.default.games.filter(function(game) {
            return 1 == game.is_recommend;
          });
          console.log(array);
          this.reloadGame(array);
        } else {
          var array = AppData_1.default.games.filter(function(game) {
            return game.match.name == item.name;
          });
          this.reloadGame(array);
        }
      };
      AppGame.prototype.requestGames = function(match_ids) {
        var _this = this;
        var games = ApiNet_1.default.post("/index/play/all", {});
        games.then(function(res) {
          if (0 == res.code) {
            AppData_1.default.games = res.data;
            _this.reloadGame(AppData_1.default.games);
          }
        }).catch(function(error) {
          setTimeout(function() {
            Helper_1.hideLoading();
          }, 500);
        });
      };
      AppGame.prototype.reloadGame = function(data) {
        var _this = this;
        this.games = [];
        for (var i = 0; i < data.length; i++) {
          var item = data[i];
          var gameItemModel = new GameItemModel_1.default();
          gameItemModel.index = i;
          gameItemModel.match = item.match.name;
          gameItemModel.teams = item.teams;
          gameItemModel.status = item.status;
          gameItemModel.play_time = item.play_time;
          gameItemModel.play_id = item.id;
          gameItemModel.action = function(x, y, delegate) {
            if (_this.currentGameItem != delegate.node) {
              _this.loadGameDetail(delegate.item);
              _this.animationToChoose(x, y, delegate);
            }
          };
          this.games.push(gameItemModel);
        }
        this.refreshGameView();
      };
      AppGame.prototype.refreshGameView = function() {
        console.log(this.games);
        this.gameList.dataList = this.games;
        this.gameList.reloadList();
        setTimeout(function() {
          Helper_1.hideLoading();
        }, 500);
      };
      AppGame.prototype.animationToChoose = function(x, y, delegate, item) {
        void 0 === item && (item = null);
        var gameItem = NodeCaches_1.default.moveItem;
        var canvas = cc.find("Canvas");
        if (delegate) {
          delegate.item.x = x;
          delegate.item.y = y;
        }
        var component = gameItem.getComponent(GameItemDelegate_1.default);
        component.item = item || delegate.item;
        gameItem.x = x;
        gameItem.y = y;
        gameItem.parent = canvas;
        gameItem.pauseSystemEvents(true);
        var finished = cc.callFunc(function() {
          component.showTag = true;
        }, this);
        gameItem.runAction(cc.sequence(cc.spawn(cc.moveTo(.3, new Vec2(0, 500)).easing(cc.easeSineIn()), cc.scaleTo(.3, 1.15, 1.15)), finished));
        delegate.node.active = false;
        this.currentGameItemDelegate = delegate;
        this.currentGameItem = gameItem;
        var logo = cc.find("logo", canvas);
        logo.runAction(cc.moveBy(.3, cc.p(0, 146)).easing(cc.easeSineIn()));
        this.router = "choose";
        var back = cc.find("back", canvas);
        var pageView = cc.find("page/pageView", canvas);
        pageView.runAction(cc.sequence(cc.delayTime(.15), cc.moveBy(.45, cc.p(-1592)).easing(cc.easeSineIn())));
        pageView.pauseSystemEvents(true);
        cc.find("menuScroll", canvas).runAction(cc.moveBy(.3, cc.p(-268)).easing(cc.easeSineIn()));
        cc.find("arrow_up", canvas).runAction(cc.fadeOut(.3));
        cc.find("arrow_down", canvas).runAction(cc.fadeOut(.3));
        AppGame_1.updateScrollIndex();
      };
      AppGame.prototype.animationBackToMain = function(isup) {
        var _this = this;
        var component = this.currentGameItem.getComponent(GameItemDelegate_1.default);
        var item = component.item;
        component.showTag = false;
        var scrollDelegate = this.sectionList.getComponent(SectionScrollDelegate_1.default);
        scrollDelegate.typeUpArrow.active = false;
        scrollDelegate.typeDownArrow.active = false;
        var finished = cc.callFunc(function() {
          _this.currentGameItem.resumeSystemEvents(true);
          _this.currentGameItem.removeFromParent();
          _this.currentGameItemDelegate && (_this.currentGameItemDelegate.node.active = true);
        }, this);
        isup ? this.currentGameItem.runAction(cc.sequence(cc.delayTime(.6), cc.spawn(cc.moveTo(.3, cc.p(0, 600)).easing(cc.easeSineIn()), cc.scaleTo(.3, 1, 1)), finished)) : this.currentGameItem.runAction(cc.sequence(cc.delayTime(.6), cc.spawn(cc.moveTo(.3, cc.p(item.x, item.y)).easing(cc.easeSineIn()), cc.scaleTo(.3, 1, 1)), finished));
        var canvas = cc.find("Canvas");
        var logo = cc.find("logo", canvas);
        var back = cc.find("back", canvas);
        logo.runAction(cc.sequence(cc.delayTime(.6), cc.moveBy(.3, cc.p(0, -146)).easing(cc.easeSineIn())));
        var pageView = cc.find("page/pageView", canvas);
        pageView.runAction(cc.sequence(cc.delayTime(.15), cc.moveBy(.45, cc.p(1592)).easing(cc.easeSineIn())));
        pageView.resumeSystemEvents(true);
        this.sectionList.runAction(cc.moveBy(.45, cc.p(1592)).easing(cc.easeSineIn()));
        cc.find("menuScroll", canvas).runAction(cc.sequence(cc.delayTime(.3), cc.moveBy(.3, cc.p(268)).easing(cc.easeSineIn())));
        cc.find("gameTypes", canvas).runAction(cc.moveBy(.3, cc.p(-268)).easing(cc.easeSineIn()));
        cc.find("arrow_up", canvas).runAction(cc.fadeIn(.3));
        cc.find("arrow_down", canvas).runAction(cc.fadeIn(.3));
        this.router = "main";
      };
      AppGame.updateMenuAtIndex = function(key) {
        var canvas = cc.find("Canvas");
        console.log(key);
      };
      AppGame.prototype.formatCurrency = function(num) {
        num = num.toString().replace(/\$|\,/g, "");
        isNaN(num) && (num = "0");
        var sign = num == (num = Math.abs(num));
        num = Math.floor(100 * num + .50000000001);
        var cents = num % 100;
        num = Math.floor(num / 100).toString();
        var centsString = "";
        centsString = cents < 10 ? "0" + cents : cents.toString();
        for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++) num = num.substring(0, num.length - (4 * i + 3)) + "," + num.substring(num.length - (4 * i + 3));
        return (sign ? "" : "-") + num + "." + centsString;
      };
      AppGame.prototype.setupView = function() {
        var userData = config_1.default.userData;
        var sectionList = NodeCaches_1.default.sectionLists.get();
        sectionList.parent = this.pageView;
        this.sectionList = sectionList;
        var component = this.sectionList.getComponent(SectionScrollDelegate_1.default);
        component.typeUpArrow = cc.find("Canvas/type_up");
        component.typeDownArrow = cc.find("Canvas/type_down");
        sectionList.x = 1592;
        sectionList.y = 0;
        console.log(userData);
        userData && (this.gold.string = this.formatCurrency(parseFloat(userData.gold)));
      };
      AppGame.prototype.loadGameDetail = function(item) {
        var _this = this;
        setTimeout(function() {
          Helper_1.showLoading();
          cc.find("Canvas/page/scrollView/view/content").active = false;
          var Canvas = cc.find("Canvas");
          ApiNet_1.default.post("/index/play/newDataList", {
            play_id: item.play_id
          }).then(function(res) {
            console.log(res);
            var data = res.data;
            _this.setupTypeScrollView(data);
            Helper_1.hideLoading();
            cc.find("gameTypes", Canvas).runAction(cc.moveBy(.3, cc.p(268)).easing(cc.easeSineIn()));
            _this.sectionList.runAction(cc.sequence(cc.delayTime(.25), cc.moveBy(.45, cc.p(-1592))).easing(cc.easeSineIn()));
          }).catch(function(error) {
            console.log(error);
            Helper_1.hideLoading();
          });
        }, 300);
      };
      AppGame.prototype.onLogSelect = function(item) {
        var _this = this;
        if ("choose" == this.router) {
          if (this.currentGameItemDelegate && this.currentGameItemDelegate.item.play_id == item.id) return;
          this.animationBackToMain(true);
          this.scheduleOnce(function() {
            var model = new GameItemModel_1.default();
            model.teams = item.teams;
            model.match = item.match.name;
            _this.animationToChoose(0, 600, null, model);
          }, .9);
        } else {
          var delegate = null;
          for (var i = 0; i < this.gameList.itemList.length; i++) {
            var del = this.gameList.itemList[i];
            if (del.item.play_id == item.id) {
              delegate = del;
              break;
            }
          }
          if (delegate) delegate.onClickItem(); else {
            var model = new GameItemModel_1.default();
            model.teams = item.teams;
            model.match = item.match.name;
            model.id = item.id;
            this.animationToChoose(0, 600, delegate, model);
          }
        }
      };
      AppGame.pageViewLoading = null;
      AppGame.numberKeyBoard = null;
      AppGame.numberSelect = null;
      AppGame.scrollIndexOffsets = [];
      AppGame.selectedMenuItem = null;
      AppGame.selectedTypeMenuItem = null;
      AppGame.matchesData = [];
      AppGame.gamesData = [];
      __decorate([ property(cc.Sprite) ], AppGame.prototype, "gunqiu", void 0);
      __decorate([ property(cc.Label) ], AppGame.prototype, "gold", void 0);
      __decorate([ property(cc.Layout) ], AppGame.prototype, "menuList", void 0);
      __decorate([ property(cc.Layout) ], AppGame.prototype, "typesList", void 0);
      __decorate([ property(ListView_1.default) ], AppGame.prototype, "gameList", void 0);
      __decorate([ property(cc.Node) ], AppGame.prototype, "pageView", void 0);
      __decorate([ property(cc.Node) ], AppGame.prototype, "typeContainer", void 0);
      __decorate([ property(cc.Node) ], AppGame.prototype, "appCanvas", void 0);
      AppGame = AppGame_1 = __decorate([ ccclass ], AppGame);
      return AppGame;
      var AppGame_1;
    }(cc.Component);
    exports.default = AppGame;
    cc._RF.pop();
  }, {
    "./AppData": "AppData",
    "./NodeCaches": "NodeCaches",
    "./alert/HelpAlert": "HelpAlert",
    "./alert/LogListAlert": "LogListAlert",
    "./alert/SearchListAlert": "SearchListAlert",
    "./alert/TipsAlert": "TipsAlert",
    "./config": "config",
    "./games/GameItemDelegate": "GameItemDelegate",
    "./games/SectionScrollDelegate": "SectionScrollDelegate",
    "./games/model/ChooseItemModel": "ChooseItemModel",
    "./games/model/GameItemModel": "GameItemModel",
    "./games/model/SectionGridItem": "SectionGridItem",
    "./menus/MenuItemDelegate": "MenuItemDelegate",
    "./utils/ApiNet": "ApiNet",
    "./utils/DateUtils": "DateUtils",
    "./utils/Helper": "Helper",
    "./widget/ListView": "ListView",
    "./widget/RecycleView": "RecycleView"
  } ],
  AppPageView: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "b13273LI0ZJ6ZA39QgAzzts", "AppPageView");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var AppPageView = function(_super) {
      __extends(AppPageView, _super);
      function AppPageView() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.scrollThreshold = .5;
        _this.autoPageTurningThreshold = 100;
        _this._curPageIdx = 0;
        _this._lastPageIdx = 0;
        _this.pageSizeHeight = 0;
        _this.enableLoopAfter = false;
        _this._pageSize = 4;
        _this.prePage = null;
        _this.currentPage = null;
        _this.nextPage = null;
        _this.pageTurningSpeed = .3;
        _this._touchBeganPosition = new cc.Vec2(0, 0);
        _this._touchEndPosition = new cc.Vec2(0, 0);
        return _this;
      }
      AppPageView.prototype._isScrollable = function(offset) {
        var offsetY = this.pageSizeHeight * this.scrollThreshold;
        return Math.abs(offset.y) >= offsetY;
      };
      AppPageView.prototype._getDragDirection = function(moveOffset) {
        if (0 === moveOffset.y) return 0;
        return moveOffset.y < 0 ? 1 : -1;
      };
      Object.defineProperty(AppPageView.prototype, "pageSize", {
        get: function() {
          return this._pageSize;
        },
        set: function(value) {
          this._pageSize = value;
          this.updateContentSize();
        },
        enumerable: true,
        configurable: true
      });
      AppPageView.prototype.updateContentSize = function() {
        this.content.height = this.pageSizeHeight * (this._pageSize + 1);
        this.scrollToPage(0, 0);
      };
      AppPageView.prototype._isQuicklyScrollable = function(touchMoveVelocity) {
        return Math.abs(touchMoveVelocity.y) > this.autoPageTurningThreshold;
      };
      AppPageView.prototype._handleReleaseLogic = function(touch) {
        var bounceBackStarted = this._startBounceBackIfNeeded();
        var moveOffset = cc.pSub(this._touchBeganPosition, this._touchEndPosition);
        if (bounceBackStarted) {
          var dragDirection = this._getDragDirection(moveOffset);
          if (0 === dragDirection) return;
          this._curPageIdx = dragDirection > 0 ? this._pageSize - 1 : 0;
        } else {
          var index = this._curPageIdx, nextIndex = index + this._getDragDirection(moveOffset);
          console.log(index, nextIndex);
          var timeInSecond = this.pageTurningSpeed * Math.abs(index - nextIndex);
          if (nextIndex < this._pageSize) {
            if (this._isScrollable(moveOffset)) {
              this.scrollToPage(nextIndex, timeInSecond);
              return;
            }
            var touchMoveVelocity = this._calculateTouchMoveVelocity();
            console.log(touchMoveVelocity);
            if (this._isQuicklyScrollable(touchMoveVelocity)) {
              this.scrollToPage(nextIndex, timeInSecond);
              return;
            }
          }
          this.scrollToPage(index, timeInSecond);
        }
      };
      AppPageView.prototype.onLoad = function() {};
      AppPageView.prototype.setCurrentPage = function(index) {
        this.scrollToPage(index, 0);
      };
      AppPageView.prototype._moveOffsetValue = function(idx) {
        var offset = cc.p(0, 0);
        offset.y = idx * this.pageSizeHeight;
        return offset;
      };
      AppPageView.prototype.scrollToPage = function(idx, timeInSecond) {
        var page = this._pageSize;
        if (idx < 0 || idx >= page) return;
        timeInSecond = void 0 !== timeInSecond ? timeInSecond : .3;
        this._lastPageIdx = this._curPageIdx;
        this._curPageIdx = idx;
        this.scrollToOffset(this._moveOffsetValue(idx), timeInSecond, true);
      };
      AppPageView.prototype._onTouchBegan = function(event, captureListeners) {
        this._touchBeganPosition = event.touch.getLocation();
        _super.prototype._onTouchBegan.call(this, event, captureListeners);
      };
      AppPageView.prototype._onTouchEnded = function(event, captureListeners) {
        this._touchEndPosition = event.touch.getLocation();
        _super.prototype._onTouchEnded.call(this, event, captureListeners);
      };
      AppPageView.prototype._onTouchCancelled = function(event, captureListeners) {
        this._touchEndPosition = event.touch.getLocation();
        _super.prototype._onTouchCancelled.call(this, event, captureListeners);
      };
      AppPageView._cellPoolCache = [];
      __decorate([ property({
        type: Number,
        slide: true,
        range: [ 0, 1, .01 ],
        tooltip: "滚动临界值"
      }) ], AppPageView.prototype, "scrollThreshold", void 0);
      __decorate([ property({
        type: Number
      }) ], AppPageView.prototype, "autoPageTurningThreshold", void 0);
      __decorate([ property({
        type: cc.Float,
        tooltip: "页高度"
      }) ], AppPageView.prototype, "pageSizeHeight", void 0);
      __decorate([ property({
        type: Boolean
      }) ], AppPageView.prototype, "enableLoopAfter", void 0);
      __decorate([ property({
        type: Number,
        tooltip: "每个页面翻页时所需时间。单位：秒"
      }) ], AppPageView.prototype, "pageTurningSpeed", void 0);
      AppPageView = __decorate([ ccclass ], AppPageView);
      return AppPageView;
    }(cc.ScrollView);
    exports.default = AppPageView;
    cc._RF.pop();
  }, {} ],
  AudioButton: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "4985coebRVA/LbEm5GnvIv+", "AudioButton");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var AppData_1 = require("../AppData");
    var Helper_1 = require("../utils/Helper");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var AudioButton = function(_super) {
      __extends(AudioButton, _super);
      function AudioButton() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.enableSprite = null;
        _this.enableDisabled = null;
        _this._isEnableSound = true;
        return _this;
      }
      Object.defineProperty(AudioButton.prototype, "isEnableSound", {
        get: function() {
          return this._isEnableSound;
        },
        set: function(value) {
          this._isEnableSound = value;
          this.updateAudio();
        },
        enumerable: true,
        configurable: true
      });
      AudioButton.prototype.updateAudio = function() {
        this.enableSprite.node.active = this.isEnableSound;
        this.enableDisabled.node.active = !this.isEnableSound;
        this.toggleSound();
      };
      AudioButton.prototype.toggleSound = function() {
        cc.sys.localStorage.setItem("sound", this.isEnableSound ? "1" : "0");
        this.isEnableSound || cc.audioEngine.stopAll();
        AppData_1.default.enableSound = this.isEnableSound;
      };
      AudioButton.prototype.onLoad = function() {
        var sound = cc.sys.localStorage.getItem("sound");
        sound && (this.isEnableSound = 1 == parseInt(sound));
        AppData_1.default.enableSound = this.isEnableSound;
      };
      AudioButton.prototype.start = function() {
        var _this = this;
        this.node.on("click", function() {
          Helper_1.playBtnSoundAway();
          _this.isEnableSound = !_this.isEnableSound;
          Helper_1.playBackgroundSound();
        });
      };
      __decorate([ property(cc.Sprite) ], AudioButton.prototype, "enableSprite", void 0);
      __decorate([ property(cc.Sprite) ], AudioButton.prototype, "enableDisabled", void 0);
      __decorate([ property ], AudioButton.prototype, "isEnableSound", null);
      AudioButton = __decorate([ ccclass ], AudioButton);
      return AudioButton;
    }(cc.Component);
    exports.default = AudioButton;
    cc._RF.pop();
  }, {
    "../AppData": "AppData",
    "../utils/Helper": "Helper"
  } ],
  BuyAlert: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "4366furo+FJv4DrJ1k2OUwP", "BuyAlert");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyItemDelegate_1 = require("./BuyItemDelegate");
    var easeBackOut = cc.easeBackOut;
    var easeBackIn = cc.easeBackIn;
    var Helper_1 = require("../utils/Helper");
    var AppData_1 = require("../AppData");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var BuyAlert = function() {
      function BuyAlert() {}
      BuyAlert_1 = BuyAlert;
      BuyAlert.show = function(title, type, model, enterAction, speed) {
        var _this = this;
        if (null != this._alert) return;
        this._speed = speed || this._speed;
        this.type = type;
        cc.loader.loadRes("prefabs/AlertContent", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          BuyAlert_1._alert = cc.instantiate(prefab);
          var cbFadeOut = cc.callFunc(BuyAlert_1.onFadeOutFinish, _this);
          var cbFadeIn = cc.callFunc(BuyAlert_1.onFadeInFinish, _this);
          BuyAlert_1.actionFadeIn = cc.sequence(cc.spawn(cc.fadeIn(_this._speed), cc.scaleTo(_this._speed, 1.1).easing(easeBackOut())), cbFadeIn);
          BuyAlert_1.actionFadeOut = cc.sequence(cc.spawn(cc.fadeOut(_this._speed), cc.scaleTo(_this._speed, .5).easing(easeBackIn())), cbFadeOut);
          BuyAlert_1._titleLabel = cc.find("alert/titleLabel", BuyAlert_1._alert).getComponent(cc.Label);
          BuyAlert_1._cancelButton = cc.find("alert/btnCancel", BuyAlert_1._alert);
          BuyAlert_1._enterButton = cc.find("alert/btnSubmit", BuyAlert_1._alert);
          BuyAlert_1._content = cc.find("alert/content", BuyAlert_1._alert);
          BuyAlert_1._alertDialog = cc.find("alert", BuyAlert_1._alert);
          BuyAlert_1._mask = cc.find("mask", BuyAlert_1._alert);
          BuyAlert_1.setupType(model);
          _this._keyboardContainer = cc.find("keyboardContainer", _this._alert);
          BuyAlert_1._enterButton.on("click", BuyAlert_1.onButtonClicked, _this);
          BuyAlert_1._cancelButton.on("click", BuyAlert_1.onButtonClicked, _this);
          BuyAlert_1._alert.parent = cc.find("Canvas");
          BuyAlert_1.startFadeIn();
          BuyAlert_1.configAlert(title, enterAction, speed);
        });
      };
      BuyAlert.stopCloseAction = function() {
        if (BuyAlert_1._enterButton && BuyAlert_1._cancelButton) {
          BuyAlert_1._enterButton.pauseSystemEvents(true);
          BuyAlert_1._cancelButton.pauseSystemEvents(true);
        }
      };
      BuyAlert.resumeCloseAction = function() {
        if (BuyAlert_1._enterButton && BuyAlert_1._cancelButton) {
          BuyAlert_1._enterButton.resumeSystemEvents(true);
          BuyAlert_1._cancelButton.resumeSystemEvents(true);
        }
      };
      BuyAlert.setupType = function(model) {
        if (BuyAlert_1.type > 0) switch (BuyAlert_1.type) {
         case AppData_1.GridType.TYPE_DUYING:
          BuyAlert_1.setupContent(model, 3);
          break;

         case AppData_1.GridType.TYPE_BODAN:
         case AppData_1.GridType.TYPE_ZHUDUI:
         case AppData_1.GridType.TYPE_KEDUI:
         case AppData_1.GridType.TYPE_ZONG:
          BuyAlert_1.setupContent(model, 1);
          break;

         case AppData_1.GridType.TYPE_RANG:
         case AppData_1.GridType.TYPE_DAXIAO:
         case AppData_1.GridType.TYPE_DANSHUANG:
          BuyAlert_1.setupContent(model, 2);
        }
      };
      BuyAlert.setupContent = function(model, number) {
        var _this = this;
        cc.loader.loadRes("prefabs/buySelectItem", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          var node = cc.find("alert", _this._alert);
          if (2 == number) {
            node.width = 984;
            BuyAlert_1._cancelButton.x = 448;
          } else if (3 == number) {
            node.width = 1428;
            BuyAlert_1._cancelButton.x = 672;
          } else if (1 == number) {
            node.width = 640;
            BuyAlert_1._cancelButton.x = 281;
          }
          BuyAlert_1._content.removeAllChildren();
          for (var i = 0; i < number; i++) {
            var buyItem = cc.instantiate(prefab);
            buyItem.parent = BuyAlert_1._content;
            buyItem.width = (1 == number, 444);
            var component = buyItem.getComponent(BuyItemDelegate_1.default);
            component.index = i;
            component.item = model;
          }
        });
      };
      BuyAlert.onButtonClicked = function(event) {
        Helper_1.playBtnSound();
        if ("btnSubmit" == event.target.name) {
          if (BuyAlert_1._enterAction) {
            var close = BuyAlert_1._enterAction();
            close && BuyAlert_1.startFadeOut();
          }
        } else BuyAlert_1.startFadeOut();
      };
      BuyAlert.startFadeOut = function() {
        Helper_1.playAlertCloseSound();
        BuyAlert_1._alertDialog.pauseSystemEvents(true);
        BuyAlert_1._mask.runAction(cc.fadeOut(BuyAlert_1._speed));
        BuyAlert_1._alertDialog.runAction(BuyAlert_1.actionFadeOut);
      };
      BuyAlert.configAlert = function(title, enterCallBack, animSpeed) {
        BuyAlert_1._enterAction = enterCallBack;
        BuyAlert_1._titleLabel.string = title;
        BuyAlert_1._enterButton.x = 0;
      };
      BuyAlert.onFadeInFinish = function() {
        BuyAlert_1._alertDialog.resumeSystemEvents(true);
      };
      BuyAlert.onFadeOutFinish = function() {
        this.onDestory();
      };
      BuyAlert.onDestory = function() {
        BuyAlert_1._alert.destroy();
        BuyAlert_1._enterAction = null;
        BuyAlert_1._alert = null;
        BuyAlert_1._titleLabel = null;
        BuyAlert_1._cancelButton = null;
        BuyAlert_1._enterButton = null;
        BuyAlert_1._speed = .3;
      };
      BuyAlert.startFadeIn = function() {
        Helper_1.playAlertOpenSound();
        BuyAlert_1._alertDialog.pauseSystemEvents(true);
        BuyAlert_1._alertDialog.position = cc.p(0, 0);
        BuyAlert_1._alertDialog.setScale(.5);
        BuyAlert_1._alertDialog.opacity = 0;
        BuyAlert_1._mask.opacity = 0;
        BuyAlert_1._mask.runAction(cc.fadeTo(BuyAlert_1._speed, 160));
        BuyAlert_1._alertDialog.runAction(BuyAlert_1.actionFadeIn);
      };
      BuyAlert._alert = null;
      BuyAlert._titleLabel = null;
      BuyAlert._cancelButton = null;
      BuyAlert._enterButton = null;
      BuyAlert._content = null;
      BuyAlert._speed = .3;
      BuyAlert.actionFadeIn = null;
      BuyAlert._enterAction = null;
      BuyAlert.actionFadeOut = null;
      BuyAlert.type = 0;
      BuyAlert._alertDialog = null;
      BuyAlert._mask = null;
      BuyAlert._keyboardContainer = null;
      BuyAlert = BuyAlert_1 = __decorate([ ccclass ], BuyAlert);
      return BuyAlert;
      var BuyAlert_1;
    }();
    exports.default = BuyAlert;
    cc._RF.pop();
  }, {
    "../AppData": "AppData",
    "../utils/Helper": "Helper",
    "./BuyItemDelegate": "BuyItemDelegate"
  } ],
  BuyItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "072d2jiwn1O9aqntj+1Vgb5", "BuyItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var NumberInput_1 = require("./NumberInput");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var BuyItemDelegate = function(_super) {
      __extends(BuyItemDelegate, _super);
      function BuyItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.gameNameLabel = null;
        _this.matchLabel = null;
        _this.teamLabel = null;
        _this.pointLabel = null;
        _this.minLabel = null;
        _this.maxLabel = null;
        _this.moneyLabel = null;
        _this.numberInput = null;
        _this._item = null;
        _this.index = 0;
        _this.target_key = "";
        _this.value = 0;
        _this.target_item = "";
        return _this;
      }
      Object.defineProperty(BuyItemDelegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      BuyItemDelegate.prototype.start = function() {
        var _this = this;
        cc.find("input_bg/input", this.node).on("onNumberChange", function(event) {
          console.log(event);
          var number = event.detail;
          _this.onNumberChange(number);
        });
      };
      BuyItemDelegate.prototype.updateData = function() {
        var name = this.item.name;
        name = name.replace("#team_home_name#", this.item.teams[0].name);
        name = name.replace("#team_guest_name#", this.item.teams[1].name);
        this.gameNameLabel.string = name;
        this.item.teams.length > 0 ? this.teamLabel.string = this.item.teams[0].name + "VS" + this.item.teams[1].name : this.teamLabel.string = "";
        if (0 == this.item.type) {
          this.matchLabel.string = "让球";
          this.target_key = 0 == this.index ? "home" : "guest";
          this.value = 0 == this.index ? parseFloat(this.item.home) : parseFloat(this.item.guest);
          this.pointLabel.string = this.item.teams[this.index].name + " " + (0 == this.index ? "让" : "受让") + this.item["type_name"] + "@" + (0 == this.index ? this.item.home : this.item.guest);
        }
        if (1 == this.item.type) {
          this.matchLabel.string = "独赢";
          switch (this.index) {
           case 0:
            this.target_key = "home";
            this.value = parseFloat(this.item.home);
            this.pointLabel.string = this.item.teams[0].name + "@" + parseFloat(this.item.home).toFixed(2);
            break;

           case 1:
            this.target_key = "same";
            this.value = parseFloat(this.item.same);
            this.pointLabel.string = "平@" + parseFloat(this.item.same).toFixed(2);
            break;

           case 2:
            this.target_key = "guest";
            this.value = parseFloat(this.item.guest);
            this.pointLabel.string = this.item.teams[1].name + "@" + parseFloat(this.item.guest).toFixed(2);
          }
        }
        if (2 == this.item.type) {
          this.matchLabel.string = "大小";
          this.target_key = 0 == this.index ? "home" : "guest";
          this.value = 0 == this.index ? parseFloat(this.item.home) : parseFloat(this.item.guest);
          this.pointLabel.string = 0 == this.index ? "大 " + this.item.under + "@" + parseFloat(this.item.home).toFixed(2) : "小 " + this.item.under + "@" + parseFloat(this.item.guest).toFixed(2);
        }
        if (3 == this.item.type) {
          this.matchLabel.string = "单双";
          this.target_key = 0 == this.index ? "sd_1" : "sd_2";
          this.value = 0 == this.index ? parseFloat(this.item.sd_1) : parseFloat(this.item.sd_2);
          this.pointLabel.string = 0 == this.index ? "单@" + this.item.sd_1 : "双@" + this.item.sd_2;
        }
        if (4 == this.item.type) {
          this.matchLabel.string = "波胆";
          this.target_key = this.item.target_key;
          this.target_item = this.item.target_item;
          this.pointLabel.string = this.item.label + "@" + parseFloat(this.item.value).toFixed(2);
          this.value = parseFloat(this.item.value);
        }
        if (5 == this.item.type) {
          this.matchLabel.string = "主队进球数";
          this.target_key = this.item.target_key;
          this.target_item = this.item.target_item;
          this.pointLabel.string = this.item.teams[0].name + " " + this.item.label + "球@" + parseFloat(this.item.value).toFixed(2);
          this.value = parseFloat(this.item.value);
        }
        if (6 == this.item.type) {
          this.matchLabel.string = "客队进球数";
          this.target_key = this.item.target_key;
          this.target_item = this.item.target_item;
          this.pointLabel.string = this.item.teams[1].name + " " + this.item.label + "球@" + parseFloat(this.item.value).toFixed(2);
          this.value = parseFloat(this.item.value);
        }
        if (7 == this.item.type) {
          this.matchLabel.string = "全场进球数";
          this.target_key = this.item.target_key;
          this.target_item = this.item.target_item;
          this.pointLabel.string = this.item.label + "球@" + parseFloat(this.item.value).toFixed(2);
          this.value = parseFloat(this.item.value);
        }
        this.numberInput.placeHolder.string = "单注100-10000";
        this.minLabel.string = "最小投注:" + this.item.min_bet;
        this.maxLabel.string = "最大投注:" + this.item.max_bet;
      };
      BuyItemDelegate.prototype.onNumberChange = function(number) {
        console.log(number);
        this.moneyLabel.string = (number * this.value).toFixed(2);
      };
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "gameNameLabel", void 0);
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "matchLabel", void 0);
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "teamLabel", void 0);
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "pointLabel", void 0);
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "minLabel", void 0);
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "maxLabel", void 0);
      __decorate([ property(cc.Label) ], BuyItemDelegate.prototype, "moneyLabel", void 0);
      __decorate([ property(NumberInput_1.default) ], BuyItemDelegate.prototype, "numberInput", void 0);
      BuyItemDelegate = __decorate([ ccclass ], BuyItemDelegate);
      return BuyItemDelegate;
    }(cc.Component);
    exports.default = BuyItemDelegate;
    cc._RF.pop();
  }, {
    "./NumberInput": "NumberInput"
  } ],
  BuyUtils: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "bf372dzoAhLt5ONmRJIm7BL", "BuyUtils");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyItemDelegate_1 = require("../alert/BuyItemDelegate");
    var NumberInput_1 = require("../alert/NumberInput");
    var ApiNet_1 = require("./ApiNet");
    var TipsAlert_1 = require("../alert/TipsAlert");
    function buy() {
      var data = [];
      var arena_id = "";
      var hasMin = false;
      BuyAlert_1.default._content && BuyAlert_1.default._content.children.forEach(function(item) {
        var numberInput = cc.find("input_bg/input", item).getComponent(NumberInput_1.default);
        var component = item.getComponent(BuyItemDelegate_1.default);
        var money = numberInput.number;
        if (parseInt(money) < 100) {
          hasMin = true;
          return;
        }
        arena_id = component.item.arena_id;
        console.log(money, component.item);
        if ("" != money && parseFloat(money) > 0) {
          var bet_item = {
            target: component.target_key,
            item: component.target_item,
            money: parseFloat(money).toFixed(2)
          };
          data.push(bet_item);
        }
      });
      if (hasMin) {
        TipsAlert_1.default.show(.3, "错误", "金额未到达下注限额，请提高下注金额再试！", function() {});
        return false;
      }
      if (data.length > 0) {
        ApiNet_1.default.post("/index/arena/bets", {
          arena_id: arena_id,
          bet_data: Base64.encode(JSON.stringify(data))
        }).then(function(res) {
          console.log(res);
          0 == res.code ? TipsAlert_1.default.show(.3, "交易结果", "投注成功，请等比赛结束", function() {}) : TipsAlert_1.default.show(.3, "交易结果", "" + res.msg, null);
        });
        return true;
      }
      TipsAlert_1.default.show(.3, "错误", "请至少选择一项下注", function() {});
      return false;
    }
    exports.buy = buy;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "../alert/BuyItemDelegate": "BuyItemDelegate",
    "../alert/NumberInput": "NumberInput",
    "../alert/TipsAlert": "TipsAlert",
    "./ApiNet": "ApiNet"
  } ],
  1: [ function(require, module, exports) {
    "use strict";
    exports.byteLength = byteLength;
    exports.toByteArray = toByteArray;
    exports.fromByteArray = fromByteArray;
    var lookup = [];
    var revLookup = [];
    var Arr = "undefined" !== typeof Uint8Array ? Uint8Array : Array;
    var code = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    for (var i = 0, len = code.length; i < len; ++i) {
      lookup[i] = code[i];
      revLookup[code.charCodeAt(i)] = i;
    }
    revLookup["-".charCodeAt(0)] = 62;
    revLookup["_".charCodeAt(0)] = 63;
    function placeHoldersCount(b64) {
      var len = b64.length;
      if (len % 4 > 0) throw new Error("Invalid string. Length must be a multiple of 4");
      return "=" === b64[len - 2] ? 2 : "=" === b64[len - 1] ? 1 : 0;
    }
    function byteLength(b64) {
      return 3 * b64.length / 4 - placeHoldersCount(b64);
    }
    function toByteArray(b64) {
      var i, l, tmp, placeHolders, arr;
      var len = b64.length;
      placeHolders = placeHoldersCount(b64);
      arr = new Arr(3 * len / 4 - placeHolders);
      l = placeHolders > 0 ? len - 4 : len;
      var L = 0;
      for (i = 0; i < l; i += 4) {
        tmp = revLookup[b64.charCodeAt(i)] << 18 | revLookup[b64.charCodeAt(i + 1)] << 12 | revLookup[b64.charCodeAt(i + 2)] << 6 | revLookup[b64.charCodeAt(i + 3)];
        arr[L++] = tmp >> 16 & 255;
        arr[L++] = tmp >> 8 & 255;
        arr[L++] = 255 & tmp;
      }
      if (2 === placeHolders) {
        tmp = revLookup[b64.charCodeAt(i)] << 2 | revLookup[b64.charCodeAt(i + 1)] >> 4;
        arr[L++] = 255 & tmp;
      } else if (1 === placeHolders) {
        tmp = revLookup[b64.charCodeAt(i)] << 10 | revLookup[b64.charCodeAt(i + 1)] << 4 | revLookup[b64.charCodeAt(i + 2)] >> 2;
        arr[L++] = tmp >> 8 & 255;
        arr[L++] = 255 & tmp;
      }
      return arr;
    }
    function tripletToBase64(num) {
      return lookup[num >> 18 & 63] + lookup[num >> 12 & 63] + lookup[num >> 6 & 63] + lookup[63 & num];
    }
    function encodeChunk(uint8, start, end) {
      var tmp;
      var output = [];
      for (var i = start; i < end; i += 3) {
        tmp = (uint8[i] << 16 & 16711680) + (uint8[i + 1] << 8 & 65280) + (255 & uint8[i + 2]);
        output.push(tripletToBase64(tmp));
      }
      return output.join("");
    }
    function fromByteArray(uint8) {
      var tmp;
      var len = uint8.length;
      var extraBytes = len % 3;
      var output = "";
      var parts = [];
      var maxChunkLength = 16383;
      for (var i = 0, len2 = len - extraBytes; i < len2; i += maxChunkLength) parts.push(encodeChunk(uint8, i, i + maxChunkLength > len2 ? len2 : i + maxChunkLength));
      if (1 === extraBytes) {
        tmp = uint8[len - 1];
        output += lookup[tmp >> 2];
        output += lookup[tmp << 4 & 63];
        output += "==";
      } else if (2 === extraBytes) {
        tmp = (uint8[len - 2] << 8) + uint8[len - 1];
        output += lookup[tmp >> 10];
        output += lookup[tmp >> 4 & 63];
        output += lookup[tmp << 2 & 63];
        output += "=";
      }
      parts.push(output);
      return parts.join("");
    }
  }, {} ],
  2: [ function(require, module, exports) {
    (function(global) {
      "use strict";
      var base64 = require("base64-js");
      var ieee754 = require("ieee754");
      var isArray = require("isarray");
      exports.Buffer = Buffer;
      exports.SlowBuffer = SlowBuffer;
      exports.INSPECT_MAX_BYTES = 50;
      Buffer.TYPED_ARRAY_SUPPORT = void 0 !== global.TYPED_ARRAY_SUPPORT ? global.TYPED_ARRAY_SUPPORT : typedArraySupport();
      exports.kMaxLength = kMaxLength();
      function typedArraySupport() {
        try {
          var arr = new Uint8Array(1);
          arr.__proto__ = {
            __proto__: Uint8Array.prototype,
            foo: function() {
              return 42;
            }
          };
          return 42 === arr.foo() && "function" === typeof arr.subarray && 0 === arr.subarray(1, 1).byteLength;
        } catch (e) {
          return false;
        }
      }
      function kMaxLength() {
        return Buffer.TYPED_ARRAY_SUPPORT ? 2147483647 : 1073741823;
      }
      function createBuffer(that, length) {
        if (kMaxLength() < length) throw new RangeError("Invalid typed array length");
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          that = new Uint8Array(length);
          that.__proto__ = Buffer.prototype;
        } else {
          null === that && (that = new Buffer(length));
          that.length = length;
        }
        return that;
      }
      function Buffer(arg, encodingOrOffset, length) {
        if (!Buffer.TYPED_ARRAY_SUPPORT && !(this instanceof Buffer)) return new Buffer(arg, encodingOrOffset, length);
        if ("number" === typeof arg) {
          if ("string" === typeof encodingOrOffset) throw new Error("If encoding is specified then the first argument must be a string");
          return allocUnsafe(this, arg);
        }
        return from(this, arg, encodingOrOffset, length);
      }
      Buffer.poolSize = 8192;
      Buffer._augment = function(arr) {
        arr.__proto__ = Buffer.prototype;
        return arr;
      };
      function from(that, value, encodingOrOffset, length) {
        if ("number" === typeof value) throw new TypeError('"value" argument must not be a number');
        if ("undefined" !== typeof ArrayBuffer && value instanceof ArrayBuffer) return fromArrayBuffer(that, value, encodingOrOffset, length);
        if ("string" === typeof value) return fromString(that, value, encodingOrOffset);
        return fromObject(that, value);
      }
      Buffer.from = function(value, encodingOrOffset, length) {
        return from(null, value, encodingOrOffset, length);
      };
      if (Buffer.TYPED_ARRAY_SUPPORT) {
        Buffer.prototype.__proto__ = Uint8Array.prototype;
        Buffer.__proto__ = Uint8Array;
        "undefined" !== typeof Symbol && Symbol.species && Buffer[Symbol.species] === Buffer && Object.defineProperty(Buffer, Symbol.species, {
          value: null,
          configurable: true
        });
      }
      function assertSize(size) {
        if ("number" !== typeof size) throw new TypeError('"size" argument must be a number');
        if (size < 0) throw new RangeError('"size" argument must not be negative');
      }
      function alloc(that, size, fill, encoding) {
        assertSize(size);
        if (size <= 0) return createBuffer(that, size);
        if (void 0 !== fill) return "string" === typeof encoding ? createBuffer(that, size).fill(fill, encoding) : createBuffer(that, size).fill(fill);
        return createBuffer(that, size);
      }
      Buffer.alloc = function(size, fill, encoding) {
        return alloc(null, size, fill, encoding);
      };
      function allocUnsafe(that, size) {
        assertSize(size);
        that = createBuffer(that, size < 0 ? 0 : 0 | checked(size));
        if (!Buffer.TYPED_ARRAY_SUPPORT) for (var i = 0; i < size; ++i) that[i] = 0;
        return that;
      }
      Buffer.allocUnsafe = function(size) {
        return allocUnsafe(null, size);
      };
      Buffer.allocUnsafeSlow = function(size) {
        return allocUnsafe(null, size);
      };
      function fromString(that, string, encoding) {
        "string" === typeof encoding && "" !== encoding || (encoding = "utf8");
        if (!Buffer.isEncoding(encoding)) throw new TypeError('"encoding" must be a valid string encoding');
        var length = 0 | byteLength(string, encoding);
        that = createBuffer(that, length);
        var actual = that.write(string, encoding);
        actual !== length && (that = that.slice(0, actual));
        return that;
      }
      function fromArrayLike(that, array) {
        var length = array.length < 0 ? 0 : 0 | checked(array.length);
        that = createBuffer(that, length);
        for (var i = 0; i < length; i += 1) that[i] = 255 & array[i];
        return that;
      }
      function fromArrayBuffer(that, array, byteOffset, length) {
        array.byteLength;
        if (byteOffset < 0 || array.byteLength < byteOffset) throw new RangeError("'offset' is out of bounds");
        if (array.byteLength < byteOffset + (length || 0)) throw new RangeError("'length' is out of bounds");
        array = void 0 === byteOffset && void 0 === length ? new Uint8Array(array) : void 0 === length ? new Uint8Array(array, byteOffset) : new Uint8Array(array, byteOffset, length);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          that = array;
          that.__proto__ = Buffer.prototype;
        } else that = fromArrayLike(that, array);
        return that;
      }
      function fromObject(that, obj) {
        if (Buffer.isBuffer(obj)) {
          var len = 0 | checked(obj.length);
          that = createBuffer(that, len);
          if (0 === that.length) return that;
          obj.copy(that, 0, 0, len);
          return that;
        }
        if (obj) {
          if ("undefined" !== typeof ArrayBuffer && obj.buffer instanceof ArrayBuffer || "length" in obj) {
            if ("number" !== typeof obj.length || isnan(obj.length)) return createBuffer(that, 0);
            return fromArrayLike(that, obj);
          }
          if ("Buffer" === obj.type && isArray(obj.data)) return fromArrayLike(that, obj.data);
        }
        throw new TypeError("First argument must be a string, Buffer, ArrayBuffer, Array, or array-like object.");
      }
      function checked(length) {
        if (length >= kMaxLength()) throw new RangeError("Attempt to allocate Buffer larger than maximum size: 0x" + kMaxLength().toString(16) + " bytes");
        return 0 | length;
      }
      function SlowBuffer(length) {
        +length != length && (length = 0);
        return Buffer.alloc(+length);
      }
      Buffer.isBuffer = function isBuffer(b) {
        return !!(null != b && b._isBuffer);
      };
      Buffer.compare = function compare(a, b) {
        if (!Buffer.isBuffer(a) || !Buffer.isBuffer(b)) throw new TypeError("Arguments must be Buffers");
        if (a === b) return 0;
        var x = a.length;
        var y = b.length;
        for (var i = 0, len = Math.min(x, y); i < len; ++i) if (a[i] !== b[i]) {
          x = a[i];
          y = b[i];
          break;
        }
        if (x < y) return -1;
        if (y < x) return 1;
        return 0;
      };
      Buffer.isEncoding = function isEncoding(encoding) {
        switch (String(encoding).toLowerCase()) {
         case "hex":
         case "utf8":
         case "utf-8":
         case "ascii":
         case "latin1":
         case "binary":
         case "base64":
         case "ucs2":
         case "ucs-2":
         case "utf16le":
         case "utf-16le":
          return true;

         default:
          return false;
        }
      };
      Buffer.concat = function concat(list, length) {
        if (!isArray(list)) throw new TypeError('"list" argument must be an Array of Buffers');
        if (0 === list.length) return Buffer.alloc(0);
        var i;
        if (void 0 === length) {
          length = 0;
          for (i = 0; i < list.length; ++i) length += list[i].length;
        }
        var buffer = Buffer.allocUnsafe(length);
        var pos = 0;
        for (i = 0; i < list.length; ++i) {
          var buf = list[i];
          if (!Buffer.isBuffer(buf)) throw new TypeError('"list" argument must be an Array of Buffers');
          buf.copy(buffer, pos);
          pos += buf.length;
        }
        return buffer;
      };
      function byteLength(string, encoding) {
        if (Buffer.isBuffer(string)) return string.length;
        if ("undefined" !== typeof ArrayBuffer && "function" === typeof ArrayBuffer.isView && (ArrayBuffer.isView(string) || string instanceof ArrayBuffer)) return string.byteLength;
        "string" !== typeof string && (string = "" + string);
        var len = string.length;
        if (0 === len) return 0;
        var loweredCase = false;
        for (;;) switch (encoding) {
         case "ascii":
         case "latin1":
         case "binary":
          return len;

         case "utf8":
         case "utf-8":
         case void 0:
          return utf8ToBytes(string).length;

         case "ucs2":
         case "ucs-2":
         case "utf16le":
         case "utf-16le":
          return 2 * len;

         case "hex":
          return len >>> 1;

         case "base64":
          return base64ToBytes(string).length;

         default:
          if (loweredCase) return utf8ToBytes(string).length;
          encoding = ("" + encoding).toLowerCase();
          loweredCase = true;
        }
      }
      Buffer.byteLength = byteLength;
      function slowToString(encoding, start, end) {
        var loweredCase = false;
        (void 0 === start || start < 0) && (start = 0);
        if (start > this.length) return "";
        (void 0 === end || end > this.length) && (end = this.length);
        if (end <= 0) return "";
        end >>>= 0;
        start >>>= 0;
        if (end <= start) return "";
        encoding || (encoding = "utf8");
        while (true) switch (encoding) {
         case "hex":
          return hexSlice(this, start, end);

         case "utf8":
         case "utf-8":
          return utf8Slice(this, start, end);

         case "ascii":
          return asciiSlice(this, start, end);

         case "latin1":
         case "binary":
          return latin1Slice(this, start, end);

         case "base64":
          return base64Slice(this, start, end);

         case "ucs2":
         case "ucs-2":
         case "utf16le":
         case "utf-16le":
          return utf16leSlice(this, start, end);

         default:
          if (loweredCase) throw new TypeError("Unknown encoding: " + encoding);
          encoding = (encoding + "").toLowerCase();
          loweredCase = true;
        }
      }
      Buffer.prototype._isBuffer = true;
      function swap(b, n, m) {
        var i = b[n];
        b[n] = b[m];
        b[m] = i;
      }
      Buffer.prototype.swap16 = function swap16() {
        var len = this.length;
        if (len % 2 !== 0) throw new RangeError("Buffer size must be a multiple of 16-bits");
        for (var i = 0; i < len; i += 2) swap(this, i, i + 1);
        return this;
      };
      Buffer.prototype.swap32 = function swap32() {
        var len = this.length;
        if (len % 4 !== 0) throw new RangeError("Buffer size must be a multiple of 32-bits");
        for (var i = 0; i < len; i += 4) {
          swap(this, i, i + 3);
          swap(this, i + 1, i + 2);
        }
        return this;
      };
      Buffer.prototype.swap64 = function swap64() {
        var len = this.length;
        if (len % 8 !== 0) throw new RangeError("Buffer size must be a multiple of 64-bits");
        for (var i = 0; i < len; i += 8) {
          swap(this, i, i + 7);
          swap(this, i + 1, i + 6);
          swap(this, i + 2, i + 5);
          swap(this, i + 3, i + 4);
        }
        return this;
      };
      Buffer.prototype.toString = function toString() {
        var length = 0 | this.length;
        if (0 === length) return "";
        if (0 === arguments.length) return utf8Slice(this, 0, length);
        return slowToString.apply(this, arguments);
      };
      Buffer.prototype.equals = function equals(b) {
        if (!Buffer.isBuffer(b)) throw new TypeError("Argument must be a Buffer");
        if (this === b) return true;
        return 0 === Buffer.compare(this, b);
      };
      Buffer.prototype.inspect = function inspect() {
        var str = "";
        var max = exports.INSPECT_MAX_BYTES;
        if (this.length > 0) {
          str = this.toString("hex", 0, max).match(/.{2}/g).join(" ");
          this.length > max && (str += " ... ");
        }
        return "<Buffer " + str + ">";
      };
      Buffer.prototype.compare = function compare(target, start, end, thisStart, thisEnd) {
        if (!Buffer.isBuffer(target)) throw new TypeError("Argument must be a Buffer");
        void 0 === start && (start = 0);
        void 0 === end && (end = target ? target.length : 0);
        void 0 === thisStart && (thisStart = 0);
        void 0 === thisEnd && (thisEnd = this.length);
        if (start < 0 || end > target.length || thisStart < 0 || thisEnd > this.length) throw new RangeError("out of range index");
        if (thisStart >= thisEnd && start >= end) return 0;
        if (thisStart >= thisEnd) return -1;
        if (start >= end) return 1;
        start >>>= 0;
        end >>>= 0;
        thisStart >>>= 0;
        thisEnd >>>= 0;
        if (this === target) return 0;
        var x = thisEnd - thisStart;
        var y = end - start;
        var len = Math.min(x, y);
        var thisCopy = this.slice(thisStart, thisEnd);
        var targetCopy = target.slice(start, end);
        for (var i = 0; i < len; ++i) if (thisCopy[i] !== targetCopy[i]) {
          x = thisCopy[i];
          y = targetCopy[i];
          break;
        }
        if (x < y) return -1;
        if (y < x) return 1;
        return 0;
      };
      function bidirectionalIndexOf(buffer, val, byteOffset, encoding, dir) {
        if (0 === buffer.length) return -1;
        if ("string" === typeof byteOffset) {
          encoding = byteOffset;
          byteOffset = 0;
        } else byteOffset > 2147483647 ? byteOffset = 2147483647 : byteOffset < -2147483648 && (byteOffset = -2147483648);
        byteOffset = +byteOffset;
        isNaN(byteOffset) && (byteOffset = dir ? 0 : buffer.length - 1);
        byteOffset < 0 && (byteOffset = buffer.length + byteOffset);
        if (byteOffset >= buffer.length) {
          if (dir) return -1;
          byteOffset = buffer.length - 1;
        } else if (byteOffset < 0) {
          if (!dir) return -1;
          byteOffset = 0;
        }
        "string" === typeof val && (val = Buffer.from(val, encoding));
        if (Buffer.isBuffer(val)) {
          if (0 === val.length) return -1;
          return arrayIndexOf(buffer, val, byteOffset, encoding, dir);
        }
        if ("number" === typeof val) {
          val &= 255;
          if (Buffer.TYPED_ARRAY_SUPPORT && "function" === typeof Uint8Array.prototype.indexOf) return dir ? Uint8Array.prototype.indexOf.call(buffer, val, byteOffset) : Uint8Array.prototype.lastIndexOf.call(buffer, val, byteOffset);
          return arrayIndexOf(buffer, [ val ], byteOffset, encoding, dir);
        }
        throw new TypeError("val must be string, number or Buffer");
      }
      function arrayIndexOf(arr, val, byteOffset, encoding, dir) {
        var indexSize = 1;
        var arrLength = arr.length;
        var valLength = val.length;
        if (void 0 !== encoding) {
          encoding = String(encoding).toLowerCase();
          if ("ucs2" === encoding || "ucs-2" === encoding || "utf16le" === encoding || "utf-16le" === encoding) {
            if (arr.length < 2 || val.length < 2) return -1;
            indexSize = 2;
            arrLength /= 2;
            valLength /= 2;
            byteOffset /= 2;
          }
        }
        function read(buf, i) {
          return 1 === indexSize ? buf[i] : buf.readUInt16BE(i * indexSize);
        }
        var i;
        if (dir) {
          var foundIndex = -1;
          for (i = byteOffset; i < arrLength; i++) if (read(arr, i) === read(val, -1 === foundIndex ? 0 : i - foundIndex)) {
            -1 === foundIndex && (foundIndex = i);
            if (i - foundIndex + 1 === valLength) return foundIndex * indexSize;
          } else {
            -1 !== foundIndex && (i -= i - foundIndex);
            foundIndex = -1;
          }
        } else {
          byteOffset + valLength > arrLength && (byteOffset = arrLength - valLength);
          for (i = byteOffset; i >= 0; i--) {
            var found = true;
            for (var j = 0; j < valLength; j++) if (read(arr, i + j) !== read(val, j)) {
              found = false;
              break;
            }
            if (found) return i;
          }
        }
        return -1;
      }
      Buffer.prototype.includes = function includes(val, byteOffset, encoding) {
        return -1 !== this.indexOf(val, byteOffset, encoding);
      };
      Buffer.prototype.indexOf = function indexOf(val, byteOffset, encoding) {
        return bidirectionalIndexOf(this, val, byteOffset, encoding, true);
      };
      Buffer.prototype.lastIndexOf = function lastIndexOf(val, byteOffset, encoding) {
        return bidirectionalIndexOf(this, val, byteOffset, encoding, false);
      };
      function hexWrite(buf, string, offset, length) {
        offset = Number(offset) || 0;
        var remaining = buf.length - offset;
        if (length) {
          length = Number(length);
          length > remaining && (length = remaining);
        } else length = remaining;
        var strLen = string.length;
        if (strLen % 2 !== 0) throw new TypeError("Invalid hex string");
        length > strLen / 2 && (length = strLen / 2);
        for (var i = 0; i < length; ++i) {
          var parsed = parseInt(string.substr(2 * i, 2), 16);
          if (isNaN(parsed)) return i;
          buf[offset + i] = parsed;
        }
        return i;
      }
      function utf8Write(buf, string, offset, length) {
        return blitBuffer(utf8ToBytes(string, buf.length - offset), buf, offset, length);
      }
      function asciiWrite(buf, string, offset, length) {
        return blitBuffer(asciiToBytes(string), buf, offset, length);
      }
      function latin1Write(buf, string, offset, length) {
        return asciiWrite(buf, string, offset, length);
      }
      function base64Write(buf, string, offset, length) {
        return blitBuffer(base64ToBytes(string), buf, offset, length);
      }
      function ucs2Write(buf, string, offset, length) {
        return blitBuffer(utf16leToBytes(string, buf.length - offset), buf, offset, length);
      }
      Buffer.prototype.write = function write(string, offset, length, encoding) {
        if (void 0 === offset) {
          encoding = "utf8";
          length = this.length;
          offset = 0;
        } else if (void 0 === length && "string" === typeof offset) {
          encoding = offset;
          length = this.length;
          offset = 0;
        } else {
          if (!isFinite(offset)) throw new Error("Buffer.write(string, encoding, offset[, length]) is no longer supported");
          offset |= 0;
          if (isFinite(length)) {
            length |= 0;
            void 0 === encoding && (encoding = "utf8");
          } else {
            encoding = length;
            length = void 0;
          }
        }
        var remaining = this.length - offset;
        (void 0 === length || length > remaining) && (length = remaining);
        if (string.length > 0 && (length < 0 || offset < 0) || offset > this.length) throw new RangeError("Attempt to write outside buffer bounds");
        encoding || (encoding = "utf8");
        var loweredCase = false;
        for (;;) switch (encoding) {
         case "hex":
          return hexWrite(this, string, offset, length);

         case "utf8":
         case "utf-8":
          return utf8Write(this, string, offset, length);

         case "ascii":
          return asciiWrite(this, string, offset, length);

         case "latin1":
         case "binary":
          return latin1Write(this, string, offset, length);

         case "base64":
          return base64Write(this, string, offset, length);

         case "ucs2":
         case "ucs-2":
         case "utf16le":
         case "utf-16le":
          return ucs2Write(this, string, offset, length);

         default:
          if (loweredCase) throw new TypeError("Unknown encoding: " + encoding);
          encoding = ("" + encoding).toLowerCase();
          loweredCase = true;
        }
      };
      Buffer.prototype.toJSON = function toJSON() {
        return {
          type: "Buffer",
          data: Array.prototype.slice.call(this._arr || this, 0)
        };
      };
      function base64Slice(buf, start, end) {
        return 0 === start && end === buf.length ? base64.fromByteArray(buf) : base64.fromByteArray(buf.slice(start, end));
      }
      function utf8Slice(buf, start, end) {
        end = Math.min(buf.length, end);
        var res = [];
        var i = start;
        while (i < end) {
          var firstByte = buf[i];
          var codePoint = null;
          var bytesPerSequence = firstByte > 239 ? 4 : firstByte > 223 ? 3 : firstByte > 191 ? 2 : 1;
          if (i + bytesPerSequence <= end) {
            var secondByte, thirdByte, fourthByte, tempCodePoint;
            switch (bytesPerSequence) {
             case 1:
              firstByte < 128 && (codePoint = firstByte);
              break;

             case 2:
              secondByte = buf[i + 1];
              if (128 === (192 & secondByte)) {
                tempCodePoint = (31 & firstByte) << 6 | 63 & secondByte;
                tempCodePoint > 127 && (codePoint = tempCodePoint);
              }
              break;

             case 3:
              secondByte = buf[i + 1];
              thirdByte = buf[i + 2];
              if (128 === (192 & secondByte) && 128 === (192 & thirdByte)) {
                tempCodePoint = (15 & firstByte) << 12 | (63 & secondByte) << 6 | 63 & thirdByte;
                tempCodePoint > 2047 && (tempCodePoint < 55296 || tempCodePoint > 57343) && (codePoint = tempCodePoint);
              }
              break;

             case 4:
              secondByte = buf[i + 1];
              thirdByte = buf[i + 2];
              fourthByte = buf[i + 3];
              if (128 === (192 & secondByte) && 128 === (192 & thirdByte) && 128 === (192 & fourthByte)) {
                tempCodePoint = (15 & firstByte) << 18 | (63 & secondByte) << 12 | (63 & thirdByte) << 6 | 63 & fourthByte;
                tempCodePoint > 65535 && tempCodePoint < 1114112 && (codePoint = tempCodePoint);
              }
            }
          }
          if (null === codePoint) {
            codePoint = 65533;
            bytesPerSequence = 1;
          } else if (codePoint > 65535) {
            codePoint -= 65536;
            res.push(codePoint >>> 10 & 1023 | 55296);
            codePoint = 56320 | 1023 & codePoint;
          }
          res.push(codePoint);
          i += bytesPerSequence;
        }
        return decodeCodePointsArray(res);
      }
      var MAX_ARGUMENTS_LENGTH = 4096;
      function decodeCodePointsArray(codePoints) {
        var len = codePoints.length;
        if (len <= MAX_ARGUMENTS_LENGTH) return String.fromCharCode.apply(String, codePoints);
        var res = "";
        var i = 0;
        while (i < len) res += String.fromCharCode.apply(String, codePoints.slice(i, i += MAX_ARGUMENTS_LENGTH));
        return res;
      }
      function asciiSlice(buf, start, end) {
        var ret = "";
        end = Math.min(buf.length, end);
        for (var i = start; i < end; ++i) ret += String.fromCharCode(127 & buf[i]);
        return ret;
      }
      function latin1Slice(buf, start, end) {
        var ret = "";
        end = Math.min(buf.length, end);
        for (var i = start; i < end; ++i) ret += String.fromCharCode(buf[i]);
        return ret;
      }
      function hexSlice(buf, start, end) {
        var len = buf.length;
        (!start || start < 0) && (start = 0);
        (!end || end < 0 || end > len) && (end = len);
        var out = "";
        for (var i = start; i < end; ++i) out += toHex(buf[i]);
        return out;
      }
      function utf16leSlice(buf, start, end) {
        var bytes = buf.slice(start, end);
        var res = "";
        for (var i = 0; i < bytes.length; i += 2) res += String.fromCharCode(bytes[i] + 256 * bytes[i + 1]);
        return res;
      }
      Buffer.prototype.slice = function slice(start, end) {
        var len = this.length;
        start = ~~start;
        end = void 0 === end ? len : ~~end;
        if (start < 0) {
          start += len;
          start < 0 && (start = 0);
        } else start > len && (start = len);
        if (end < 0) {
          end += len;
          end < 0 && (end = 0);
        } else end > len && (end = len);
        end < start && (end = start);
        var newBuf;
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          newBuf = this.subarray(start, end);
          newBuf.__proto__ = Buffer.prototype;
        } else {
          var sliceLen = end - start;
          newBuf = new Buffer(sliceLen, void 0);
          for (var i = 0; i < sliceLen; ++i) newBuf[i] = this[i + start];
        }
        return newBuf;
      };
      function checkOffset(offset, ext, length) {
        if (offset % 1 !== 0 || offset < 0) throw new RangeError("offset is not uint");
        if (offset + ext > length) throw new RangeError("Trying to access beyond buffer length");
      }
      Buffer.prototype.readUIntLE = function readUIntLE(offset, byteLength, noAssert) {
        offset |= 0;
        byteLength |= 0;
        noAssert || checkOffset(offset, byteLength, this.length);
        var val = this[offset];
        var mul = 1;
        var i = 0;
        while (++i < byteLength && (mul *= 256)) val += this[offset + i] * mul;
        return val;
      };
      Buffer.prototype.readUIntBE = function readUIntBE(offset, byteLength, noAssert) {
        offset |= 0;
        byteLength |= 0;
        noAssert || checkOffset(offset, byteLength, this.length);
        var val = this[offset + --byteLength];
        var mul = 1;
        while (byteLength > 0 && (mul *= 256)) val += this[offset + --byteLength] * mul;
        return val;
      };
      Buffer.prototype.readUInt8 = function readUInt8(offset, noAssert) {
        noAssert || checkOffset(offset, 1, this.length);
        return this[offset];
      };
      Buffer.prototype.readUInt16LE = function readUInt16LE(offset, noAssert) {
        noAssert || checkOffset(offset, 2, this.length);
        return this[offset] | this[offset + 1] << 8;
      };
      Buffer.prototype.readUInt16BE = function readUInt16BE(offset, noAssert) {
        noAssert || checkOffset(offset, 2, this.length);
        return this[offset] << 8 | this[offset + 1];
      };
      Buffer.prototype.readUInt32LE = function readUInt32LE(offset, noAssert) {
        noAssert || checkOffset(offset, 4, this.length);
        return (this[offset] | this[offset + 1] << 8 | this[offset + 2] << 16) + 16777216 * this[offset + 3];
      };
      Buffer.prototype.readUInt32BE = function readUInt32BE(offset, noAssert) {
        noAssert || checkOffset(offset, 4, this.length);
        return 16777216 * this[offset] + (this[offset + 1] << 16 | this[offset + 2] << 8 | this[offset + 3]);
      };
      Buffer.prototype.readIntLE = function readIntLE(offset, byteLength, noAssert) {
        offset |= 0;
        byteLength |= 0;
        noAssert || checkOffset(offset, byteLength, this.length);
        var val = this[offset];
        var mul = 1;
        var i = 0;
        while (++i < byteLength && (mul *= 256)) val += this[offset + i] * mul;
        mul *= 128;
        val >= mul && (val -= Math.pow(2, 8 * byteLength));
        return val;
      };
      Buffer.prototype.readIntBE = function readIntBE(offset, byteLength, noAssert) {
        offset |= 0;
        byteLength |= 0;
        noAssert || checkOffset(offset, byteLength, this.length);
        var i = byteLength;
        var mul = 1;
        var val = this[offset + --i];
        while (i > 0 && (mul *= 256)) val += this[offset + --i] * mul;
        mul *= 128;
        val >= mul && (val -= Math.pow(2, 8 * byteLength));
        return val;
      };
      Buffer.prototype.readInt8 = function readInt8(offset, noAssert) {
        noAssert || checkOffset(offset, 1, this.length);
        if (!(128 & this[offset])) return this[offset];
        return -1 * (255 - this[offset] + 1);
      };
      Buffer.prototype.readInt16LE = function readInt16LE(offset, noAssert) {
        noAssert || checkOffset(offset, 2, this.length);
        var val = this[offset] | this[offset + 1] << 8;
        return 32768 & val ? 4294901760 | val : val;
      };
      Buffer.prototype.readInt16BE = function readInt16BE(offset, noAssert) {
        noAssert || checkOffset(offset, 2, this.length);
        var val = this[offset + 1] | this[offset] << 8;
        return 32768 & val ? 4294901760 | val : val;
      };
      Buffer.prototype.readInt32LE = function readInt32LE(offset, noAssert) {
        noAssert || checkOffset(offset, 4, this.length);
        return this[offset] | this[offset + 1] << 8 | this[offset + 2] << 16 | this[offset + 3] << 24;
      };
      Buffer.prototype.readInt32BE = function readInt32BE(offset, noAssert) {
        noAssert || checkOffset(offset, 4, this.length);
        return this[offset] << 24 | this[offset + 1] << 16 | this[offset + 2] << 8 | this[offset + 3];
      };
      Buffer.prototype.readFloatLE = function readFloatLE(offset, noAssert) {
        noAssert || checkOffset(offset, 4, this.length);
        return ieee754.read(this, offset, true, 23, 4);
      };
      Buffer.prototype.readFloatBE = function readFloatBE(offset, noAssert) {
        noAssert || checkOffset(offset, 4, this.length);
        return ieee754.read(this, offset, false, 23, 4);
      };
      Buffer.prototype.readDoubleLE = function readDoubleLE(offset, noAssert) {
        noAssert || checkOffset(offset, 8, this.length);
        return ieee754.read(this, offset, true, 52, 8);
      };
      Buffer.prototype.readDoubleBE = function readDoubleBE(offset, noAssert) {
        noAssert || checkOffset(offset, 8, this.length);
        return ieee754.read(this, offset, false, 52, 8);
      };
      function checkInt(buf, value, offset, ext, max, min) {
        if (!Buffer.isBuffer(buf)) throw new TypeError('"buffer" argument must be a Buffer instance');
        if (value > max || value < min) throw new RangeError('"value" argument is out of bounds');
        if (offset + ext > buf.length) throw new RangeError("Index out of range");
      }
      Buffer.prototype.writeUIntLE = function writeUIntLE(value, offset, byteLength, noAssert) {
        value = +value;
        offset |= 0;
        byteLength |= 0;
        if (!noAssert) {
          var maxBytes = Math.pow(2, 8 * byteLength) - 1;
          checkInt(this, value, offset, byteLength, maxBytes, 0);
        }
        var mul = 1;
        var i = 0;
        this[offset] = 255 & value;
        while (++i < byteLength && (mul *= 256)) this[offset + i] = value / mul & 255;
        return offset + byteLength;
      };
      Buffer.prototype.writeUIntBE = function writeUIntBE(value, offset, byteLength, noAssert) {
        value = +value;
        offset |= 0;
        byteLength |= 0;
        if (!noAssert) {
          var maxBytes = Math.pow(2, 8 * byteLength) - 1;
          checkInt(this, value, offset, byteLength, maxBytes, 0);
        }
        var i = byteLength - 1;
        var mul = 1;
        this[offset + i] = 255 & value;
        while (--i >= 0 && (mul *= 256)) this[offset + i] = value / mul & 255;
        return offset + byteLength;
      };
      Buffer.prototype.writeUInt8 = function writeUInt8(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 1, 255, 0);
        Buffer.TYPED_ARRAY_SUPPORT || (value = Math.floor(value));
        this[offset] = 255 & value;
        return offset + 1;
      };
      function objectWriteUInt16(buf, value, offset, littleEndian) {
        value < 0 && (value = 65535 + value + 1);
        for (var i = 0, j = Math.min(buf.length - offset, 2); i < j; ++i) buf[offset + i] = (value & 255 << 8 * (littleEndian ? i : 1 - i)) >>> 8 * (littleEndian ? i : 1 - i);
      }
      Buffer.prototype.writeUInt16LE = function writeUInt16LE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 2, 65535, 0);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = 255 & value;
          this[offset + 1] = value >>> 8;
        } else objectWriteUInt16(this, value, offset, true);
        return offset + 2;
      };
      Buffer.prototype.writeUInt16BE = function writeUInt16BE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 2, 65535, 0);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = value >>> 8;
          this[offset + 1] = 255 & value;
        } else objectWriteUInt16(this, value, offset, false);
        return offset + 2;
      };
      function objectWriteUInt32(buf, value, offset, littleEndian) {
        value < 0 && (value = 4294967295 + value + 1);
        for (var i = 0, j = Math.min(buf.length - offset, 4); i < j; ++i) buf[offset + i] = value >>> 8 * (littleEndian ? i : 3 - i) & 255;
      }
      Buffer.prototype.writeUInt32LE = function writeUInt32LE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 4, 4294967295, 0);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset + 3] = value >>> 24;
          this[offset + 2] = value >>> 16;
          this[offset + 1] = value >>> 8;
          this[offset] = 255 & value;
        } else objectWriteUInt32(this, value, offset, true);
        return offset + 4;
      };
      Buffer.prototype.writeUInt32BE = function writeUInt32BE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 4, 4294967295, 0);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = value >>> 24;
          this[offset + 1] = value >>> 16;
          this[offset + 2] = value >>> 8;
          this[offset + 3] = 255 & value;
        } else objectWriteUInt32(this, value, offset, false);
        return offset + 4;
      };
      Buffer.prototype.writeIntLE = function writeIntLE(value, offset, byteLength, noAssert) {
        value = +value;
        offset |= 0;
        if (!noAssert) {
          var limit = Math.pow(2, 8 * byteLength - 1);
          checkInt(this, value, offset, byteLength, limit - 1, -limit);
        }
        var i = 0;
        var mul = 1;
        var sub = 0;
        this[offset] = 255 & value;
        while (++i < byteLength && (mul *= 256)) {
          value < 0 && 0 === sub && 0 !== this[offset + i - 1] && (sub = 1);
          this[offset + i] = (value / mul >> 0) - sub & 255;
        }
        return offset + byteLength;
      };
      Buffer.prototype.writeIntBE = function writeIntBE(value, offset, byteLength, noAssert) {
        value = +value;
        offset |= 0;
        if (!noAssert) {
          var limit = Math.pow(2, 8 * byteLength - 1);
          checkInt(this, value, offset, byteLength, limit - 1, -limit);
        }
        var i = byteLength - 1;
        var mul = 1;
        var sub = 0;
        this[offset + i] = 255 & value;
        while (--i >= 0 && (mul *= 256)) {
          value < 0 && 0 === sub && 0 !== this[offset + i + 1] && (sub = 1);
          this[offset + i] = (value / mul >> 0) - sub & 255;
        }
        return offset + byteLength;
      };
      Buffer.prototype.writeInt8 = function writeInt8(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 1, 127, -128);
        Buffer.TYPED_ARRAY_SUPPORT || (value = Math.floor(value));
        value < 0 && (value = 255 + value + 1);
        this[offset] = 255 & value;
        return offset + 1;
      };
      Buffer.prototype.writeInt16LE = function writeInt16LE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 2, 32767, -32768);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = 255 & value;
          this[offset + 1] = value >>> 8;
        } else objectWriteUInt16(this, value, offset, true);
        return offset + 2;
      };
      Buffer.prototype.writeInt16BE = function writeInt16BE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 2, 32767, -32768);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = value >>> 8;
          this[offset + 1] = 255 & value;
        } else objectWriteUInt16(this, value, offset, false);
        return offset + 2;
      };
      Buffer.prototype.writeInt32LE = function writeInt32LE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 4, 2147483647, -2147483648);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = 255 & value;
          this[offset + 1] = value >>> 8;
          this[offset + 2] = value >>> 16;
          this[offset + 3] = value >>> 24;
        } else objectWriteUInt32(this, value, offset, true);
        return offset + 4;
      };
      Buffer.prototype.writeInt32BE = function writeInt32BE(value, offset, noAssert) {
        value = +value;
        offset |= 0;
        noAssert || checkInt(this, value, offset, 4, 2147483647, -2147483648);
        value < 0 && (value = 4294967295 + value + 1);
        if (Buffer.TYPED_ARRAY_SUPPORT) {
          this[offset] = value >>> 24;
          this[offset + 1] = value >>> 16;
          this[offset + 2] = value >>> 8;
          this[offset + 3] = 255 & value;
        } else objectWriteUInt32(this, value, offset, false);
        return offset + 4;
      };
      function checkIEEE754(buf, value, offset, ext, max, min) {
        if (offset + ext > buf.length) throw new RangeError("Index out of range");
        if (offset < 0) throw new RangeError("Index out of range");
      }
      function writeFloat(buf, value, offset, littleEndian, noAssert) {
        noAssert || checkIEEE754(buf, value, offset, 4, 3.4028234663852886e38, -3.4028234663852886e38);
        ieee754.write(buf, value, offset, littleEndian, 23, 4);
        return offset + 4;
      }
      Buffer.prototype.writeFloatLE = function writeFloatLE(value, offset, noAssert) {
        return writeFloat(this, value, offset, true, noAssert);
      };
      Buffer.prototype.writeFloatBE = function writeFloatBE(value, offset, noAssert) {
        return writeFloat(this, value, offset, false, noAssert);
      };
      function writeDouble(buf, value, offset, littleEndian, noAssert) {
        noAssert || checkIEEE754(buf, value, offset, 8, 1.7976931348623157e308, -1.7976931348623157e308);
        ieee754.write(buf, value, offset, littleEndian, 52, 8);
        return offset + 8;
      }
      Buffer.prototype.writeDoubleLE = function writeDoubleLE(value, offset, noAssert) {
        return writeDouble(this, value, offset, true, noAssert);
      };
      Buffer.prototype.writeDoubleBE = function writeDoubleBE(value, offset, noAssert) {
        return writeDouble(this, value, offset, false, noAssert);
      };
      Buffer.prototype.copy = function copy(target, targetStart, start, end) {
        start || (start = 0);
        end || 0 === end || (end = this.length);
        targetStart >= target.length && (targetStart = target.length);
        targetStart || (targetStart = 0);
        end > 0 && end < start && (end = start);
        if (end === start) return 0;
        if (0 === target.length || 0 === this.length) return 0;
        if (targetStart < 0) throw new RangeError("targetStart out of bounds");
        if (start < 0 || start >= this.length) throw new RangeError("sourceStart out of bounds");
        if (end < 0) throw new RangeError("sourceEnd out of bounds");
        end > this.length && (end = this.length);
        target.length - targetStart < end - start && (end = target.length - targetStart + start);
        var len = end - start;
        var i;
        if (this === target && start < targetStart && targetStart < end) for (i = len - 1; i >= 0; --i) target[i + targetStart] = this[i + start]; else if (len < 1e3 || !Buffer.TYPED_ARRAY_SUPPORT) for (i = 0; i < len; ++i) target[i + targetStart] = this[i + start]; else Uint8Array.prototype.set.call(target, this.subarray(start, start + len), targetStart);
        return len;
      };
      Buffer.prototype.fill = function fill(val, start, end, encoding) {
        if ("string" === typeof val) {
          if ("string" === typeof start) {
            encoding = start;
            start = 0;
            end = this.length;
          } else if ("string" === typeof end) {
            encoding = end;
            end = this.length;
          }
          if (1 === val.length) {
            var code = val.charCodeAt(0);
            code < 256 && (val = code);
          }
          if (void 0 !== encoding && "string" !== typeof encoding) throw new TypeError("encoding must be a string");
          if ("string" === typeof encoding && !Buffer.isEncoding(encoding)) throw new TypeError("Unknown encoding: " + encoding);
        } else "number" === typeof val && (val &= 255);
        if (start < 0 || this.length < start || this.length < end) throw new RangeError("Out of range index");
        if (end <= start) return this;
        start >>>= 0;
        end = void 0 === end ? this.length : end >>> 0;
        val || (val = 0);
        var i;
        if ("number" === typeof val) for (i = start; i < end; ++i) this[i] = val; else {
          var bytes = Buffer.isBuffer(val) ? val : utf8ToBytes(new Buffer(val, encoding).toString());
          var len = bytes.length;
          for (i = 0; i < end - start; ++i) this[i + start] = bytes[i % len];
        }
        return this;
      };
      var INVALID_BASE64_RE = /[^+\/0-9A-Za-z-_]/g;
      function base64clean(str) {
        str = stringtrim(str).replace(INVALID_BASE64_RE, "");
        if (str.length < 2) return "";
        while (str.length % 4 !== 0) str += "=";
        return str;
      }
      function stringtrim(str) {
        if (str.trim) return str.trim();
        return str.replace(/^\s+|\s+$/g, "");
      }
      function toHex(n) {
        if (n < 16) return "0" + n.toString(16);
        return n.toString(16);
      }
      function utf8ToBytes(string, units) {
        units = units || Infinity;
        var codePoint;
        var length = string.length;
        var leadSurrogate = null;
        var bytes = [];
        for (var i = 0; i < length; ++i) {
          codePoint = string.charCodeAt(i);
          if (codePoint > 55295 && codePoint < 57344) {
            if (!leadSurrogate) {
              if (codePoint > 56319) {
                (units -= 3) > -1 && bytes.push(239, 191, 189);
                continue;
              }
              if (i + 1 === length) {
                (units -= 3) > -1 && bytes.push(239, 191, 189);
                continue;
              }
              leadSurrogate = codePoint;
              continue;
            }
            if (codePoint < 56320) {
              (units -= 3) > -1 && bytes.push(239, 191, 189);
              leadSurrogate = codePoint;
              continue;
            }
            codePoint = 65536 + (leadSurrogate - 55296 << 10 | codePoint - 56320);
          } else leadSurrogate && (units -= 3) > -1 && bytes.push(239, 191, 189);
          leadSurrogate = null;
          if (codePoint < 128) {
            if ((units -= 1) < 0) break;
            bytes.push(codePoint);
          } else if (codePoint < 2048) {
            if ((units -= 2) < 0) break;
            bytes.push(codePoint >> 6 | 192, 63 & codePoint | 128);
          } else if (codePoint < 65536) {
            if ((units -= 3) < 0) break;
            bytes.push(codePoint >> 12 | 224, codePoint >> 6 & 63 | 128, 63 & codePoint | 128);
          } else {
            if (!(codePoint < 1114112)) throw new Error("Invalid code point");
            if ((units -= 4) < 0) break;
            bytes.push(codePoint >> 18 | 240, codePoint >> 12 & 63 | 128, codePoint >> 6 & 63 | 128, 63 & codePoint | 128);
          }
        }
        return bytes;
      }
      function asciiToBytes(str) {
        var byteArray = [];
        for (var i = 0; i < str.length; ++i) byteArray.push(255 & str.charCodeAt(i));
        return byteArray;
      }
      function utf16leToBytes(str, units) {
        var c, hi, lo;
        var byteArray = [];
        for (var i = 0; i < str.length; ++i) {
          if ((units -= 2) < 0) break;
          c = str.charCodeAt(i);
          hi = c >> 8;
          lo = c % 256;
          byteArray.push(lo);
          byteArray.push(hi);
        }
        return byteArray;
      }
      function base64ToBytes(str) {
        return base64.toByteArray(base64clean(str));
      }
      function blitBuffer(src, dst, offset, length) {
        for (var i = 0; i < length; ++i) {
          if (i + offset >= dst.length || i >= src.length) break;
          dst[i + offset] = src[i];
        }
        return i;
      }
      function isnan(val) {
        return val !== val;
      }
    }).call(this, "undefined" !== typeof global ? global : "undefined" !== typeof self ? self : "undefined" !== typeof window ? window : {});
  }, {
    "base64-js": 1,
    ieee754: 4,
    isarray: 3
  } ],
  3: [ function(require, module, exports) {
    var toString = {}.toString;
    module.exports = Array.isArray || function(arr) {
      return "[object Array]" == toString.call(arr);
    };
  }, {} ],
  4: [ function(require, module, exports) {
    exports.read = function(buffer, offset, isLE, mLen, nBytes) {
      var e, m;
      var eLen = 8 * nBytes - mLen - 1;
      var eMax = (1 << eLen) - 1;
      var eBias = eMax >> 1;
      var nBits = -7;
      var i = isLE ? nBytes - 1 : 0;
      var d = isLE ? -1 : 1;
      var s = buffer[offset + i];
      i += d;
      e = s & (1 << -nBits) - 1;
      s >>= -nBits;
      nBits += eLen;
      for (;nBits > 0; e = 256 * e + buffer[offset + i], i += d, nBits -= 8) ;
      m = e & (1 << -nBits) - 1;
      e >>= -nBits;
      nBits += mLen;
      for (;nBits > 0; m = 256 * m + buffer[offset + i], i += d, nBits -= 8) ;
      if (0 === e) e = 1 - eBias; else {
        if (e === eMax) return m ? NaN : Infinity * (s ? -1 : 1);
        m += Math.pow(2, mLen);
        e -= eBias;
      }
      return (s ? -1 : 1) * m * Math.pow(2, e - mLen);
    };
    exports.write = function(buffer, value, offset, isLE, mLen, nBytes) {
      var e, m, c;
      var eLen = 8 * nBytes - mLen - 1;
      var eMax = (1 << eLen) - 1;
      var eBias = eMax >> 1;
      var rt = 23 === mLen ? Math.pow(2, -24) - Math.pow(2, -77) : 0;
      var i = isLE ? 0 : nBytes - 1;
      var d = isLE ? 1 : -1;
      var s = value < 0 || 0 === value && 1 / value < 0 ? 1 : 0;
      value = Math.abs(value);
      if (isNaN(value) || Infinity === value) {
        m = isNaN(value) ? 1 : 0;
        e = eMax;
      } else {
        e = Math.floor(Math.log(value) / Math.LN2);
        if (value * (c = Math.pow(2, -e)) < 1) {
          e--;
          c *= 2;
        }
        value += e + eBias >= 1 ? rt / c : rt * Math.pow(2, 1 - eBias);
        if (value * c >= 2) {
          e++;
          c /= 2;
        }
        if (e + eBias >= eMax) {
          m = 0;
          e = eMax;
        } else if (e + eBias >= 1) {
          m = (value * c - 1) * Math.pow(2, mLen);
          e += eBias;
        } else {
          m = value * Math.pow(2, eBias - 1) * Math.pow(2, mLen);
          e = 0;
        }
      }
      for (;mLen >= 8; buffer[offset + i] = 255 & m, i += d, m /= 256, mLen -= 8) ;
      e = e << mLen | m;
      eLen += mLen;
      for (;eLen > 0; buffer[offset + i] = 255 & e, i += d, e /= 256, eLen -= 8) ;
      buffer[offset + i - d] |= 128 * s;
    };
  }, {} ],
  ChooseItemModel: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "1f949xU2PRAuZuxOgVCm8hS", "ChooseItemModel");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ChooseItemModel = function() {
      function ChooseItemModel() {
        this.home = "";
        this.handicap = "";
        this.name = "";
        this.guest = "";
        this.same = "";
        this.over = "";
        this.under = "";
        this.min_bet = 0;
        this.max_bet = 0;
        this.type = 0;
        this.match = "";
        this.teams = [];
        this.label = "";
        this.value = "";
        this.arena_id = "";
        this.sd_1 = "";
        this.sd_2 = "";
        this.type_name = "";
        this.target = "";
      }
      ChooseItemModel = __decorate([ ccclass ], ChooseItemModel);
      return ChooseItemModel;
    }();
    exports.default = ChooseItemModel;
    cc._RF.pop();
  }, {} ],
  ChooseItemType1Delegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "dabf8/QU69MkZuYMc4YFtEs", "ChooseItemType1Delegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyUtils_1 = require("../utils/BuyUtils");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ChooseItemType1Delegate = function(_super) {
      __extends(ChooseItemType1Delegate, _super);
      function ChooseItemType1Delegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._item = null;
        _this.homeLabel = null;
        _this.guestLabel = null;
        _this.sameLabel = null;
        return _this;
      }
      Object.defineProperty(ChooseItemType1Delegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      ChooseItemType1Delegate.prototype.updateItem = function(x, y, number, model, reload) {};
      ChooseItemType1Delegate.prototype.onItemClick = function() {
        console.log(this.item);
        BuyAlert_1.default.show("交易单", 1, this.item, function() {
          return BuyUtils_1.buy();
        }, .3);
      };
      ChooseItemType1Delegate.prototype.updateData = function() {
        if (this.item) {
          this.homeLabel.string = parseFloat(this.item.home).toFixed(2) + "";
          this.guestLabel.string = parseFloat(this.item.guest).toFixed(2) + "";
          this.sameLabel.string = parseFloat(this.item.same).toFixed(2) + "";
        }
      };
      __decorate([ property(cc.Label) ], ChooseItemType1Delegate.prototype, "homeLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType1Delegate.prototype, "guestLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType1Delegate.prototype, "sameLabel", void 0);
      ChooseItemType1Delegate = __decorate([ ccclass ], ChooseItemType1Delegate);
      return ChooseItemType1Delegate;
    }(SectionGridItemDelegate_1.default);
    exports.default = ChooseItemType1Delegate;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "../utils/BuyUtils": "BuyUtils",
    "./SectionGridItemDelegate": "SectionGridItemDelegate"
  } ],
  ChooseItemType2Delegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "6a00baojQhHDqyqdHFtkR4u", "ChooseItemType2Delegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyUtils_1 = require("../utils/BuyUtils");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ChooseItemType2Delegate = function(_super) {
      __extends(ChooseItemType2Delegate, _super);
      function ChooseItemType2Delegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._item = null;
        _this.pointLabel = null;
        _this.circle = null;
        _this.oddLabel = null;
        return _this;
      }
      Object.defineProperty(ChooseItemType2Delegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      ChooseItemType2Delegate.prototype.updateItem = function(x, y, number, model, reload) {};
      ChooseItemType2Delegate.prototype.onItemClick = function() {
        console.log(this.item);
        BuyAlert_1.default.show("交易单", 2, this.item, function() {
          return BuyUtils_1.buy();
        }, .3);
      };
      ChooseItemType2Delegate.prototype.updateData = function() {
        this.item.type > 4 && this.item.type < 7 ? this.pointLabel.string = this.item.label + "球" : 7 == this.item.type ? this.pointLabel.string = 1 == this.item.label.length ? this.item.label + "+" : this.item.label : this.pointLabel.string = this.item.label;
        this.pointLabel.string.length > 2 ? this.circle.width = 96 : this.pointLabel.string.length > 1 ? this.circle.width = 80 : this.circle.width = 64;
        this.oddLabel.string = parseFloat(this.item.value).toFixed(2);
      };
      __decorate([ property(cc.Label) ], ChooseItemType2Delegate.prototype, "pointLabel", void 0);
      __decorate([ property(cc.Node) ], ChooseItemType2Delegate.prototype, "circle", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType2Delegate.prototype, "oddLabel", void 0);
      ChooseItemType2Delegate = __decorate([ ccclass ], ChooseItemType2Delegate);
      return ChooseItemType2Delegate;
    }(SectionGridItemDelegate_1.default);
    exports.default = ChooseItemType2Delegate;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "../utils/BuyUtils": "BuyUtils",
    "./SectionGridItemDelegate": "SectionGridItemDelegate"
  } ],
  ChooseItemType3Delegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "caaf2L7Jh1H2LorJw4r/YRW", "ChooseItemType3Delegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyUtils_1 = require("../utils/BuyUtils");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ChooseItemType3Delegate = function(_super) {
      __extends(ChooseItemType3Delegate, _super);
      function ChooseItemType3Delegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._item = null;
        _this.guestLabel = null;
        _this.homeLabel = null;
        _this.pointLabel = null;
        _this.circle = null;
        return _this;
      }
      Object.defineProperty(ChooseItemType3Delegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      ChooseItemType3Delegate.prototype.updateItem = function(x, y, number, model, reload) {};
      ChooseItemType3Delegate.prototype.onItemClick = function() {
        console.log(this.item);
        BuyAlert_1.default.show("交易单", 3, this.item, function() {
          return BuyUtils_1.buy();
        }, .3);
      };
      ChooseItemType3Delegate.prototype.updateData = function() {
        if (this.item) {
          this.homeLabel.string = parseFloat(this.item.home).toFixed(2);
          this.guestLabel.string = parseFloat(this.item.guest).toFixed(2);
          this.pointLabel.string = "" + this.item.handicap;
          this.pointLabel.string.length > 1 ? this.circle.width = 120 : this.circle.width = 64;
        }
      };
      __decorate([ property(cc.Label) ], ChooseItemType3Delegate.prototype, "guestLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType3Delegate.prototype, "homeLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType3Delegate.prototype, "pointLabel", void 0);
      __decorate([ property(cc.Node) ], ChooseItemType3Delegate.prototype, "circle", void 0);
      ChooseItemType3Delegate = __decorate([ ccclass ], ChooseItemType3Delegate);
      return ChooseItemType3Delegate;
    }(SectionGridItemDelegate_1.default);
    exports.default = ChooseItemType3Delegate;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "../utils/BuyUtils": "BuyUtils",
    "./SectionGridItemDelegate": "SectionGridItemDelegate"
  } ],
  ChooseItemType4Delegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "6f09fasXLdEtZVN5TA9lxCj", "ChooseItemType4Delegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyUtils_1 = require("../utils/BuyUtils");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ChooseItemType4Delegate = function(_super) {
      __extends(ChooseItemType4Delegate, _super);
      function ChooseItemType4Delegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._item = null;
        _this.homeLabel = null;
        _this.guestLabel = null;
        _this.pointLabel = null;
        _this.circle = null;
        return _this;
      }
      Object.defineProperty(ChooseItemType4Delegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      ChooseItemType4Delegate.prototype.updateItem = function(x, y, number, model, reload) {};
      ChooseItemType4Delegate.prototype.onItemClick = function() {
        console.log(this.item);
        BuyAlert_1.default.show("交易单", 4, this.item, function() {
          return BuyUtils_1.buy();
        }, .3);
      };
      ChooseItemType4Delegate.prototype.updateData = function() {
        if (this.item) {
          this.homeLabel.string = parseFloat(this.item.guest).toFixed(2) + "";
          this.guestLabel.string = parseFloat(this.item.home).toFixed(2) + "";
          this.pointLabel.string = this.item.over;
          this.pointLabel.string.length > 2 ? this.circle.width = 96 : this.pointLabel.string.length > 1 ? this.circle.width = 80 : this.circle.width = 64;
        }
      };
      __decorate([ property(cc.Label) ], ChooseItemType4Delegate.prototype, "homeLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType4Delegate.prototype, "guestLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType4Delegate.prototype, "pointLabel", void 0);
      __decorate([ property(cc.Node) ], ChooseItemType4Delegate.prototype, "circle", void 0);
      ChooseItemType4Delegate = __decorate([ ccclass ], ChooseItemType4Delegate);
      return ChooseItemType4Delegate;
    }(SectionGridItemDelegate_1.default);
    exports.default = ChooseItemType4Delegate;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "../utils/BuyUtils": "BuyUtils",
    "./SectionGridItemDelegate": "SectionGridItemDelegate"
  } ],
  ChooseItemType5Delegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "b38c3cISuRGKb+vDiWE4ra8", "ChooseItemType5Delegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyUtils_1 = require("../utils/BuyUtils");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ChooseItemType5Delegate = function(_super) {
      __extends(ChooseItemType5Delegate, _super);
      function ChooseItemType5Delegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._item = null;
        _this.homeLabel = null;
        _this.guestLabel = null;
        _this.pointLabel = null;
        return _this;
      }
      Object.defineProperty(ChooseItemType5Delegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      ChooseItemType5Delegate.prototype.updateItem = function(x, y, number, model, reload) {};
      ChooseItemType5Delegate.prototype.onItemClick = function() {
        console.log(this.item);
        BuyAlert_1.default.show("交易单", 5, this.item, function() {
          return BuyUtils_1.buy();
        }, .3);
      };
      ChooseItemType5Delegate.prototype.updateData = function() {
        this.homeLabel.string = parseFloat(this.item.sd_1).toFixed(2);
        this.guestLabel.string = parseFloat(this.item.sd_2).toFixed(2);
      };
      __decorate([ property(cc.Label) ], ChooseItemType5Delegate.prototype, "homeLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType5Delegate.prototype, "guestLabel", void 0);
      __decorate([ property(cc.Label) ], ChooseItemType5Delegate.prototype, "pointLabel", void 0);
      ChooseItemType5Delegate = __decorate([ ccclass ], ChooseItemType5Delegate);
      return ChooseItemType5Delegate;
    }(SectionGridItemDelegate_1.default);
    exports.default = ChooseItemType5Delegate;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "../utils/BuyUtils": "BuyUtils",
    "./SectionGridItemDelegate": "SectionGridItemDelegate"
  } ],
  DateUtils: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "c2536aYD8tPTos2IfWkR+mv", "DateUtils");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var DateUtils = {
      getISOYearWeek: function(date) {
        var commericalyear = this.getCommerialYear(date);
        var date2 = this.getYearFirstWeekDate(commericalyear);
        var day1 = date.getDay();
        0 == day1 && (day1 = 7);
        var day2 = date2.getDay();
        0 == day2 && (day2 = 7);
        var d = Math.round((date.getTime() - date2.getTime() + 864e5 * (day2 - day1)) / 864e5);
        return Math.floor(d / 7) + 1;
      },
      getYearFirstWeekDate: function(commericalyear) {
        var yearfirstdaydate = new Date(commericalyear, 0, 1);
        var daynum = yearfirstdaydate.getDay();
        var monthday = yearfirstdaydate.getDate();
        0 == daynum && (daynum = 7);
        return daynum <= 4 ? new Date(yearfirstdaydate.getFullYear(), yearfirstdaydate.getMonth(), monthday + 1 - daynum) : new Date(yearfirstdaydate.getFullYear(), yearfirstdaydate.getMonth(), monthday + 8 - daynum);
      },
      getCommerialYear: function(date) {
        var daynum = date.getDay();
        var monthday = date.getDate();
        0 == daynum && (daynum = 7);
        var thisthurdaydate = new Date(date.getFullYear(), date.getMonth(), monthday + 4 - daynum);
        return thisthurdaydate.getFullYear();
      },
      getWeekStartDate: function(date) {
        var nowDayOfWeek = 0 == date.getDay() ? 6 : date.getDay() - 1;
        var t = new Date(date);
        t.setTime(t.getTime() - 864e5 * nowDayOfWeek);
        return t;
      },
      getWeekEndDate: function(date) {
        var t = new Date(date);
        t.setTime(this.getWeekStartDate(date).getTime() + 5184e5);
        return t;
      },
      dateFormat: function(date, format) {
        var o = {
          "M+": date.getMonth() + 1,
          "d+": date.getDate(),
          "h+": date.getHours(),
          "m+": date.getMinutes(),
          "s+": date.getSeconds(),
          "q+": Math.floor((date.getMonth() + 3) / 3),
          S: date.getMilliseconds()
        };
        /(y+)/.test(format) && (format = format.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length)));
        for (var k in o) new RegExp("(" + k + ")").test(format) && (format = format.replace(RegExp.$1, 1 == RegExp.$1.length ? o[k] : ("00" + o[k]).substr(("" + o[k]).length)));
        return format;
      },
      isSameDate: function(date, date2) {
        return date.getFullYear() == date2.getFullYear() && date.getMonth() == date2.getMonth() && date.getDay() == date2.getDay();
      },
      isDate: function(dateString) {
        if ("" == dateString.trim()) {
          alert("日期为空！请输入格式正确的日期\n\r日期格式：yyyy-mm-dd\n\r例    如：2013-08-08\n\r");
          return false;
        }
        dateString = dateString.trim();
        var r = dateString.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/);
        if (null == r) {
          alert("请输入格式正确的日期\n\r日期格式：yyyy-mm-dd\n\r例    如：2013-08-08\n\r");
          return false;
        }
        var d = new Date(r[1], r[3] - 1, r[4]);
        var num = d.getFullYear() == r[1] && d.getMonth() + 1 == r[3] && d.getDate() == r[4];
        num && alert("请输入格式正确的日期\n\r日期格式：yyyy-mm-dd\n\r例    如：2013-08-08\n\r");
        return num;
      }
    };
    exports.default = DateUtils;
    cc._RF.pop();
  }, {} ],
  GameItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "eecd7iBbaVCXKVpRx4zqfNf", "GameItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ListItemDelegate_1 = require("../widget/ListItemDelegate");
    var Helper_1 = require("../utils/Helper");
    var TipsAlert_1 = require("../alert/TipsAlert");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var GameItemDelegate = function(_super) {
      __extends(GameItemDelegate, _super);
      function GameItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.updateTimer = 0;
        _this.updateInterval = 1;
        _this.homeTag = null;
        _this.guestTag = null;
        _this.leftLabel = null;
        _this.rightLabel = null;
        _this.nameLabel = null;
        _this.numberLabel = null;
        _this.leftIcon = null;
        _this.rightIcon = null;
        _this.timeLabel = null;
        _this.enable = true;
        _this._item = null;
        _this._showTag = false;
        return _this;
      }
      Object.defineProperty(GameItemDelegate.prototype, "showTag", {
        get: function() {
          return this._showTag;
        },
        set: function(value) {
          this._showTag = value;
          if (this.showTag) {
            this.homeTag.runAction(cc.fadeIn(.15));
            this.guestTag.runAction(cc.fadeIn(.15));
          } else {
            this.homeTag.runAction(cc.fadeOut(.15));
            this.guestTag.runAction(cc.fadeOut(.15));
          }
        },
        enumerable: true,
        configurable: true
      });
      GameItemDelegate.prototype.init = function(index, data, reload, group) {
        console.log(index);
        this.index = index;
        if (reload) if (index < data.array.length) {
          this.node.opacity = 255;
          this.item = data.array[index];
        } else {
          this.node.pauseSystemEvents(true);
          this.node.opacity = 0;
        }
      };
      Object.defineProperty(GameItemDelegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      GameItemDelegate.prototype.updateItem = function(index, x, y, model) {
        _super.prototype.updateItem.call(this, index, x, y, model);
        this.item = model;
      };
      GameItemDelegate.prototype.updateData = function() {
        var _this = this;
        if (this.item) {
          for (var i = 0; i < this.item.teams.length; i++) {
            var item = this.item.teams[i];
            if (1 == item.has_home) {
              this.leftLabel.string = item.name;
              this.leftLabel.string.length > 6 && this.leftLabel.string.length <= 8 ? this.leftLabel.fontSize = 32 : this.leftLabel.string.length > 8 ? this.leftLabel.fontSize = 28 : this.leftLabel.fontSize = 42;
              Helper_1.loadSpriteFrame(item.logo).then(function(res) {
                _this.leftIcon.spriteFrame = res.img;
              });
            } else {
              this.rightLabel.string = item.name;
              this.rightLabel.string.length > 6 && this.rightLabel.string.length <= 8 ? this.rightLabel.fontSize = 32 : this.rightLabel.string.length > 8 ? this.rightLabel.fontSize = 28 : this.rightLabel.fontSize = 42;
              Helper_1.loadSpriteFrame(item.logo).then(function(res) {
                _this.rightIcon.spriteFrame = res.img;
              });
            }
          }
          this.item.match && (this.nameLabel.string = this.item.match);
          if (this.item.play_time > 0) {
            var dayjs = window["dayjs"];
            var date = dayjs(1e3 * this.item.play_time);
            var late = dayjs().isAfter(date);
            this.numberLabel.string = late ? "比赛已开始" : date.format("MM月DD日 HH:mm");
          }
        }
      };
      GameItemDelegate.prototype.update = function(dt) {
        this.updateTimer += dt;
        if (this.updateTimer < this.updateInterval) return;
        this.updateTimer = 0;
        if (this.item && 1 == parseInt(this.item.status)) {
          var diff = 1e3 * this.item.play_time - new Date().getTime();
          if (diff > 0) {
            var day = this.formatTime(diff);
            this.timeLabel.string = day;
          } else this.timeLabel.string = "停止投注";
        } else this.timeLabel.string = "停止投注";
      };
      GameItemDelegate.prototype.formatTime = function(number) {
        var second = Math.floor(number / 1e3);
        var day = Math.floor(second / 86400);
        var temp = second % 86400;
        var hour = Math.floor(temp / 3600);
        temp %= 3600;
        var min = Math.floor(temp / 60);
        temp %= 60;
        var sec = Math.floor(temp);
        var str = day + "天 ";
        str += hour < 10 ? "0" + hour : hour;
        str += ":";
        str += min < 10 ? "0" + min : min;
        str += ":";
        str += sec < 10 ? "0" + sec : sec;
        return str;
      };
      GameItemDelegate.prototype.onClickItem = function() {
        if (0 == this.node.opacity) return;
        if (parseInt(this.item.status) > 1) {
          TipsAlert_1.default.show(.3, "提示", "本场比赛已经停止投注，感谢您的支持！", function() {});
          return;
        }
        var x = this.item.index % 2 == 0 ? -274 : 540;
        var row = Math.floor(this.item.index / 2) % 5;
        var y = 140 * (2 - row) + 20;
        if (this.item.action) {
          Helper_1.playSelectSound();
          console.log("-----");
          this.item.action(x, y, this);
        }
      };
      __decorate([ property ], GameItemDelegate.prototype, "showTag", null);
      __decorate([ property(cc.Node) ], GameItemDelegate.prototype, "homeTag", void 0);
      __decorate([ property(cc.Node) ], GameItemDelegate.prototype, "guestTag", void 0);
      __decorate([ property(cc.Label) ], GameItemDelegate.prototype, "leftLabel", void 0);
      __decorate([ property(cc.Label) ], GameItemDelegate.prototype, "rightLabel", void 0);
      __decorate([ property(cc.Label) ], GameItemDelegate.prototype, "nameLabel", void 0);
      __decorate([ property(cc.Label) ], GameItemDelegate.prototype, "numberLabel", void 0);
      __decorate([ property(cc.Sprite) ], GameItemDelegate.prototype, "leftIcon", void 0);
      __decorate([ property(cc.Sprite) ], GameItemDelegate.prototype, "rightIcon", void 0);
      __decorate([ property(cc.Label) ], GameItemDelegate.prototype, "timeLabel", void 0);
      GameItemDelegate = __decorate([ ccclass ], GameItemDelegate);
      return GameItemDelegate;
    }(ListItemDelegate_1.default);
    exports.default = GameItemDelegate;
    cc._RF.pop();
  }, {
    "../alert/TipsAlert": "TipsAlert",
    "../utils/Helper": "Helper",
    "../widget/ListItemDelegate": "ListItemDelegate"
  } ],
  GameItemModel: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "fd9dbKR06dBXKd1z7LdmWew", "GameItemModel");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var GameItemModel = function() {
      function GameItemModel() {
        this.index = 0;
        this.action = null;
        this.match = "";
        this.teams = [];
        this.play_time = 0;
        this.play_id = 0;
        this.id = 0;
      }
      GameItemModel = __decorate([ ccclass ], GameItemModel);
      return GameItemModel;
    }();
    exports.default = GameItemModel;
    cc._RF.pop();
  }, {} ],
  GamePageItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "c3953+mTd9LX61rwrMDX3ck", "GamePageItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var AppGame_1 = require("../AppGame");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var GamePageItemDelegate = function(_super) {
      __extends(GamePageItemDelegate, _super);
      function GamePageItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.items = [];
        return _this;
      }
      GamePageItemDelegate.prototype.onLoad = function() {
        var appGame = cc.find("AppGame").getComponent(AppGame_1.default);
        for (var i = 0; i < 10; i++) ;
      };
      GamePageItemDelegate.prototype.onDestroy = function() {
        var appGame = cc.find("AppGame").getComponent(AppGame_1.default);
        _super.prototype.onDestroy.call(this);
      };
      GamePageItemDelegate = __decorate([ ccclass ], GamePageItemDelegate);
      return GamePageItemDelegate;
    }(cc.Component);
    exports.default = GamePageItemDelegate;
    cc._RF.pop();
  }, {
    "../AppGame": "AppGame"
  } ],
  HelpAlert: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "f0969k2QFZCgKfSXE/+2fTQ", "HelpAlert");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var TabLayout_1 = require("../widget/TabLayout");
    var HelpTabContent_1 = require("./HelpTabContent");
    var Helper_1 = require("../utils/Helper");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var HelpAlert = function() {
      function HelpAlert() {}
      HelpAlert_1 = HelpAlert;
      HelpAlert.pre = function() {
        var _this = this;
        cc.loader.loadRes("prefabs/HelpAlert", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          _this._alert = cc.instantiate(prefab);
          _this._mask = cc.find("mask", _this._alert);
          _this._bg = cc.find("alertBg", _this._alert);
          HelpAlert_1.updateData();
          var cbFadeOut = cc.callFunc(_this.onFadeOutFinish, _this);
          var cbFadeIn = cc.callFunc(_this.onFadeInFinish, _this);
          _this.actionFadeIn = cc.sequence(cc.moveBy(_this._speed, cc.p(-_this._bg.width)), cbFadeIn);
          _this.actionFadeOut = cc.sequence(cc.moveBy(_this._speed, cc.p(_this._bg.width)), cbFadeOut);
          _this._cancelButton = cc.find("alertBg/btnCancel", _this._alert);
          _this._cancelButton.on("click", _this.onButtonClicked, _this);
          _this._alert.parent = cc.find("Canvas");
          _this._alert.active = false;
        });
      };
      HelpAlert.show = function(speed) {
        if (null == this._alert) return;
        this._speed = speed || this._speed;
        this._alert.setLocalZOrder(999);
        this._alert.active = true;
        this.startFadeIn();
      };
      HelpAlert.updateData = function() {
        var tabLayout = cc.find("alertBg/tabs", this._alert).getComponent(TabLayout_1.default);
        var tabContent = cc.find("alertBg/tabContent", this._alert).getComponent(HelpTabContent_1.default);
        tabContent.helpData = this.helpData;
        tabLayout.node.on("onTabSelected", function(event) {
          console.log(event.detail);
          tabContent.tabIndex = event.detail;
        });
      };
      HelpAlert.onButtonClicked = function(event) {
        "enterButton" == event.target.name && this._enterAction && this._enterAction();
        this.startFadeOut();
      };
      HelpAlert.startFadeOut = function() {
        Helper_1.playAlertCloseSound();
        cc.eventManager.pauseTarget(this._alert, true);
        this._mask.runAction(cc.fadeOut(this._speed));
        this._alert.runAction(this.actionFadeOut);
      };
      HelpAlert.onFadeInFinish = function() {
        cc.eventManager.resumeTarget(this._alert, true);
      };
      HelpAlert.onFadeOutFinish = function() {};
      HelpAlert.onDestory = function() {
        this._alert.destroy();
        this._enterAction = null;
        this._alert = null;
        this._titleLabel = null;
        this._cancelButton = null;
        this._enterButton = null;
        this._speed = .3;
      };
      HelpAlert.startFadeIn = function() {
        Helper_1.playAlertOpenSound();
        cc.eventManager.pauseTarget(this._alert, true);
        this._alert.position = cc.p(0, 0);
        this._mask.opacity = 0;
        this._mask.active = true;
        this._mask.runAction(cc.fadeTo(this._speed, 156));
        this._alert.runAction(this.actionFadeIn);
      };
      HelpAlert._alert = null;
      HelpAlert._mask = null;
      HelpAlert._titleLabel = null;
      HelpAlert._cancelButton = null;
      HelpAlert._enterButton = null;
      HelpAlert._speed = .3;
      HelpAlert.actionFadeIn = null;
      HelpAlert._enterAction = null;
      HelpAlert.actionFadeOut = null;
      HelpAlert._bg = null;
      HelpAlert.helpData = {
        typeData: [],
        commonData: []
      };
      HelpAlert = HelpAlert_1 = __decorate([ ccclass ], HelpAlert);
      return HelpAlert;
      var HelpAlert_1;
    }();
    exports.default = HelpAlert;
    cc._RF.pop();
  }, {
    "../utils/Helper": "Helper",
    "../widget/TabLayout": "TabLayout",
    "./HelpTabContent": "HelpTabContent"
  } ],
  HelpTabContent: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "6be93YVsOpLnpBPgjyfCk5N", "HelpTabContent");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var Tab_1 = require("../widget/Tab");
    var TabLayout_1 = require("../widget/TabLayout");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var HelpTabContent = function(_super) {
      __extends(HelpTabContent, _super);
      function HelpTabContent() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._tabIndex = 0;
        _this._tabData = [];
        _this.tab1 = null;
        _this.tab2 = null;
        _this.label1 = null;
        _this.label2 = null;
        _this._helpData = {
          typeData: [],
          commonData: []
        };
        return _this;
      }
      Object.defineProperty(HelpTabContent.prototype, "helpData", {
        get: function() {
          return this._helpData;
        },
        set: function(value) {
          this._helpData = value;
          this.updateTabs();
        },
        enumerable: true,
        configurable: true
      });
      HelpTabContent.prototype.updateTabs = function() {
        var _this = this;
        var tabLayout1 = cc.find("tips", this.tab1).getComponent(TabLayout_1.default);
        var tabLayout2 = cc.find("tips", this.tab2).getComponent(TabLayout_1.default);
        tabLayout1.node.on("onTabSelected", function(event) {
          var index = event.detail;
          _this.label1.string = _this.helpData.typeData[index].content;
        });
        cc.loader.loadRes("prefabs/tab", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          tabLayout1.clearTabs();
          tabLayout2.clearTabs();
          for (var i = 0; i < _this._helpData.typeData.length; i++) {
            var typeItem = _this._helpData.typeData[i];
            var node = cc.instantiate(prefab);
            node.parent = tabLayout1.node;
            var component = node.getComponent(Tab_1.default);
            component.label.string = typeItem.name;
            console.log(component);
            0 == i && (_this.label1.string = typeItem.content);
          }
          for (var i = 0; i < _this._helpData.commonData.length; i++) {
            var typeItem = _this._helpData.commonData[i];
            0 == i && (_this.label2.string = typeItem.content);
          }
          tabLayout1.setupTabs();
          tabLayout2.setupTabs();
        });
        this.tabIndex = 0;
      };
      Object.defineProperty(HelpTabContent.prototype, "tabIndex", {
        get: function() {
          return this._tabIndex;
        },
        set: function(value) {
          this._tabIndex = value;
          this.updateContent();
        },
        enumerable: true,
        configurable: true
      });
      HelpTabContent.prototype.updateContent = function() {
        if (this.tabIndex < this.node.childrenCount) for (var i = 0; i < this.node.childrenCount; i++) {
          var node = this.node.children[i];
          node.active = i == this.tabIndex;
        }
      };
      HelpTabContent.prototype.start = function() {};
      __decorate([ property(cc.Node) ], HelpTabContent.prototype, "tab1", void 0);
      __decorate([ property(cc.Node) ], HelpTabContent.prototype, "tab2", void 0);
      __decorate([ property(cc.RichText) ], HelpTabContent.prototype, "label1", void 0);
      __decorate([ property(cc.RichText) ], HelpTabContent.prototype, "label2", void 0);
      __decorate([ property ], HelpTabContent.prototype, "tabIndex", null);
      HelpTabContent = __decorate([ ccclass ], HelpTabContent);
      return HelpTabContent;
    }(cc.Component);
    exports.default = HelpTabContent;
    cc._RF.pop();
  }, {
    "../widget/Tab": "Tab",
    "../widget/TabLayout": "TabLayout"
  } ],
  Helper: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "3567fNIzRBO0qjAANhUD50p", "Helper");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ApiNet_1 = require("./ApiNet");
    var config_1 = require("../config");
    var AppGame_1 = require("../AppGame");
    var HelpAlert_1 = require("../alert/HelpAlert");
    var AppData_1 = require("../AppData");
    function startWith(string, start) {
      return -1 != string.indexOf(start);
    }
    exports.startWith = startWith;
    function isRemoteRes(url) {
      return startWith(url, "http://") || startWith(url, "https://");
    }
    exports.isRemoteRes = isRemoteRes;
    function loadSpriteFrame(url) {
      return new Promise(function(resolve, reject) {
        var spriteFrame = AppData_1.default.imagesCaches.get(url);
        if (spriteFrame) resolve({
          img: spriteFrame,
          url: url
        }); else if (isRemoteRes(url)) if (cc.sys.isNative) cc.loader.load(url, function(error, texture) {
          if (error) {
            console.log(error);
            reject(error);
          } else {
            var spriteFrame_1 = new cc.SpriteFrame(texture);
            resolve({
              img: spriteFrame_1,
              url: url
            });
            AppData_1.default.imagesCaches.set(url, spriteFrame_1);
          }
        }); else {
          url = url.replace(config_1.default.IMG_URL, "/imgs");
          var img_1 = new Image();
          img_1.src = url;
          img_1.crossOrigin = "anonymous";
          img_1.onload = function() {
            var texture = new cc.Texture2D();
            texture.initWithElement(img_1);
            texture.handleLoadedTexture();
            var sf = new cc.SpriteFrame(texture);
            AppData_1.default.imagesCaches.set(url, sf);
            resolve({
              img: sf,
              url: url
            });
          };
        } else cc.loader.loadRes(url, cc.SpriteFrame, function(error, texture) {
          if (error) {
            console.log(error);
            reject(error);
          } else {
            resolve({
              img: texture,
              url: url
            });
            AppData_1.default.imagesCaches.set(url, texture);
          }
        });
      });
    }
    exports.loadSpriteFrame = loadSpriteFrame;
    var displayImage = {
      target: null,
      load: function(url) {
        var _this = this;
        loadSpriteFrame(url).then(function(res) {
          (res.url = _this.target.url) && (_this.target.sprite.spriteFrame = res.img);
        });
        return this;
      },
      to: function(target) {
        this.target = target;
      }
    };
    exports.displayImage = displayImage;
    function showKeyBoard() {
      AppGame_1.default.numberKeyBoard.opacity = 0;
      AppGame_1.default.numberKeyBoard.setScale(.5, .5);
      AppGame_1.default.numberKeyBoard.runAction(cc.spawn(cc.fadeIn(.3), cc.scaleTo(.3, 1).easing(cc.easeBackOut())));
    }
    exports.showKeyBoard = showKeyBoard;
    function hideKeyBoard() {
      AppGame_1.default.numberKeyBoard.opacity = 255;
      AppGame_1.default.numberKeyBoard.setScale(1, 1);
      var finished = cc.callFunc(function() {
        AppGame_1.default.numberKeyBoard.removeFromParent();
      }, this);
      AppGame_1.default.numberKeyBoard.runAction(cc.sequence(cc.spawn(cc.fadeOut(.3), cc.scaleTo(.3, .5).easing(cc.easeBackIn())), finished));
    }
    exports.hideKeyBoard = hideKeyBoard;
    function setupGames(gameRes) {
      if (0 == gameRes.code) {
        var data = gameRes.data;
        AppGame_1.default.gamesData = data;
      }
    }
    function pagination(pageNo, pageSize, array) {
      var offset = (pageNo - 1) * pageSize;
      return offset + pageSize >= array.length ? array.slice(offset, array.length) : array.slice(offset, offset + pageSize);
    }
    exports.pagination = pagination;
    function playBackgroundSound() {
      AppData_1.default.enableSound && cc.audioEngine.play(AppData_1.default.sound.bg, true, .4);
    }
    exports.playBackgroundSound = playBackgroundSound;
    function playMenuSelectSound() {
      AppData_1.default.enableSound && cc.audioEngine.play(AppData_1.default.sound.menu, false, .4);
    }
    exports.playMenuSelectSound = playMenuSelectSound;
    function playBtnSoundAway() {
      cc.audioEngine.play(AppData_1.default.sound.btn, false, .8);
    }
    exports.playBtnSoundAway = playBtnSoundAway;
    function playBtnSound() {
      AppData_1.default.enableSound && cc.audioEngine.play(AppData_1.default.sound.btn, false, .8);
    }
    exports.playBtnSound = playBtnSound;
    function playSelectSound() {
      AppData_1.default.enableSound && cc.audioEngine.play(AppData_1.default.sound.select, false, .8);
    }
    exports.playSelectSound = playSelectSound;
    function playAlertOpenSound() {
      AppData_1.default.enableSound && cc.audioEngine.play(AppData_1.default.sound.open, false, 1);
    }
    exports.playAlertOpenSound = playAlertOpenSound;
    function playAlertCloseSound() {
      AppData_1.default.enableSound && cc.audioEngine.play(AppData_1.default.sound.close, false, 1);
    }
    exports.playAlertCloseSound = playAlertCloseSound;
    function exitGame() {
      jsb.reflection.callStaticMethod("org/cocos2dx/javascript/AppActivity", "endGame", "()V");
    }
    exports.exitGame = exitGame;
    function loadHelp() {
      Promise.all([ ApiNet_1.default.post("/index/common/helplist", {
        type_id: 1
      }), ApiNet_1.default.post("/index/common/helplist", {
        type_id: 2
      }) ]).then(function(res) {
        var response = res[0];
        var response2 = res[1];
        0 == response.code && (HelpAlert_1.default.helpData.typeData = response.data);
        0 == response2.code && (HelpAlert_1.default.helpData.commonData = response2.data);
        HelpAlert_1.default.pre();
      }).catch(function(error) {});
    }
    exports.loadHelp = loadHelp;
    function showLoading() {
      AppGame_1.default.pageViewLoading ? cc.find("Canvas").addChild(AppGame_1.default.pageViewLoading) : cc.loader.loadRes("loading/mask", cc.Prefab, function(error, prefab) {
        if (error) cc.error(error); else {
          var mask = cc.instantiate(prefab);
          AppGame_1.default.pageViewLoading = mask;
          AppGame_1.default.pageViewLoading.parent = cc.find("Canvas");
        }
      });
    }
    exports.showLoading = showLoading;
    function loadGame() {}
    exports.loadGame = loadGame;
    function hideLoading() {
      AppGame_1.default.pageViewLoading && AppGame_1.default.pageViewLoading.removeFromParent();
    }
    exports.hideLoading = hideLoading;
    function loadGameData() {
      return new Promise(function(resolve, reject) {
        var matches = ApiNet_1.default.post("/index/common/matchs", {});
        matches.then(function(res) {
          AppData_1.default.matches = AppData_1.default.matches.concat(res.data);
          AppData_1.default.matches.map(function(item) {
            item["logo_hover"] = item.logo + "_selected";
          });
          console.log(AppData_1.default.matches);
          resolve("加载成功");
        }).catch(function(error) {
          console.log(error);
          reject(error);
        });
      });
    }
    exports.loadGameData = loadGameData;
    cc._RF.pop();
  }, {
    "../AppData": "AppData",
    "../AppGame": "AppGame",
    "../alert/HelpAlert": "HelpAlert",
    "../config": "config",
    "./ApiNet": "ApiNet"
  } ],
  HotUpdate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "dcd25zSJ1tDv7uLP1hB2+Rg", "HotUpdate");
    "use strict";
    var _TipsAlert = require("../alert/TipsAlert");
    var _TipsAlert2 = _interopRequireDefault(_TipsAlert);
    function _interopRequireDefault(obj) {
      return obj && obj.__esModule ? obj : {
        default: obj
      };
    }
    cc.Class({
      extends: cc.Component,
      properties: {
        manifestUrl: cc.RawAsset,
        _updating: false,
        _canRetry: false,
        _storagePath: "",
        callback: null,
        progressCallback: null
      },
      checkCb: function checkCb(event) {
        cc.log("Code: " + event.getEventCode());
        var needUpdate = false;
        switch (event.getEventCode()) {
         case jsb.EventAssetsManager.ERROR_NO_LOCAL_MANIFEST:
          needUpdate = false;
          cc.log("No local manifest file found, hot update skipped.");
          break;

         case jsb.EventAssetsManager.ERROR_DOWNLOAD_MANIFEST:
         case jsb.EventAssetsManager.ERROR_PARSE_MANIFEST:
          needUpdate = false;
          cc.log("Fail to download manifest file, hot update skipped.");
          break;

         case jsb.EventAssetsManager.ALREADY_UP_TO_DATE:
          needUpdate = false;
          cc.log("Already up to date with the latest remote version.");
          break;

         case jsb.EventAssetsManager.NEW_VERSION_FOUND:
          cc.log("New version found, please try to update.");
          needUpdate = true;
          break;

         default:
          return;
        }
        this.callback && this.callback(needUpdate);
        cc.eventManager.removeListener(this._checkListener);
        this._checkListener = null;
        this._updating = false;
      },
      updateCb: function updateCb(event) {
        var needRestart = false;
        var failed = false;
        switch (event.getEventCode()) {
         case jsb.EventAssetsManager.ERROR_NO_LOCAL_MANIFEST:
          cc.log("No local manifest file found, hot update skipped.");
          failed = true;
          break;

         case jsb.EventAssetsManager.UPDATE_PROGRESSION:
          cc.log(event.getPercent() / 100 + "% : " + msg);
          var msg = event.getMessage();
          msg && cc.log("Updated file." + msg);
          this.progressCallback && this.progressCallback(event.getPercent() / 100);
          break;

         case jsb.EventAssetsManager.ERROR_DOWNLOAD_MANIFEST:
         case jsb.EventAssetsManager.ERROR_PARSE_MANIFEST:
          cc.log("Fail to download manifest file, hot update skipped.");
          failed = true;
          break;

         case jsb.EventAssetsManager.ALREADY_UP_TO_DATE:
          cc.log("Already up to date with the latest remote version.");
          failed = true;
          break;

         case jsb.EventAssetsManager.UPDATE_FINISHED:
          cc.log("Update finished. " + event.getMessage());
          needRestart = true;
          break;

         case jsb.EventAssetsManager.UPDATE_FAILED:
          cc.log("Update failed. " + event.getMessage());
          this.panel.retryBtn.active = true;
          this._updating = false;
          this._canRetry = true;
          break;

         case jsb.EventAssetsManager.ERROR_UPDATING:
          cc.log("Asset update error: " + event.getAssetId() + ", " + event.getMessage());
          break;

         case jsb.EventAssetsManager.ERROR_DECOMPRESS:
          cc.log(event.getMessage());
        }
        if (failed) {
          cc.eventManager.removeListener(this._updateListener);
          this._updateListener = null;
          this._updating = false;
        }
        if (needRestart) {
          cc.eventManager.removeListener(this._updateListener);
          this._updateListener = null;
          var searchPaths = jsb.fileUtils.getSearchPaths();
          var newPaths = this._am.getLocalManifest().getSearchPaths();
          console.log(JSON.stringify(newPaths));
          Array.prototype.unshift(searchPaths, newPaths);
          cc.sys.localStorage.setItem("HotUpdateSearchPaths", JSON.stringify(searchPaths));
          jsb.fileUtils.setSearchPaths(searchPaths);
          _TipsAlert2.default.show(.3, "更新成功", "游戏更新成功，游戏即将重启", function() {
            cc.game.restart();
          });
          cc.audioEngine.stopAll();
        }
      },
      loadCustomManifest: function loadCustomManifest() {
        if (this._am.getState() === jsb.AssetsManager.State.UNINITED) {
          var manifest = new jsb.Manifest(customManifestStr, this._storagePath);
          this._am.loadLocalManifest(manifest, this._storagePath);
          cc.log("Using custom manifest");
        }
      },
      retry: function retry() {
        if (!this._updating && this._canRetry) {
          this.panel.retryBtn.active = false;
          this._canRetry = false;
          cc.log("Retry failed Assets...");
          this._am.downloadFailedAssets();
        }
      },
      checkUpdate: function checkUpdate() {
        if (this._updating) {
          cc.log("Checking or updating ...");
          return;
        }
        this._am.getState() === jsb.AssetsManager.State.UNINITED && this._am.loadLocalManifest(this.manifestUrl);
        if (!this._am.getLocalManifest() || !this._am.getLocalManifest().isLoaded()) {
          cc.log("Failed to load local manifest ...");
          return;
        }
        this._checkListener = new jsb.EventListenerAssetsManager(this._am, this.checkCb.bind(this));
        cc.eventManager.addListener(this._checkListener, 1);
        this._am.checkUpdate();
        this._updating = true;
      },
      hotUpdate: function hotUpdate() {
        if (this._am && !this._updating) {
          this._updateListener = new jsb.EventListenerAssetsManager(this._am, this.updateCb.bind(this));
          cc.eventManager.addListener(this._updateListener, 1);
          this._am.getState() === jsb.AssetsManager.State.UNINITED && this._am.loadLocalManifest(this.manifestUrl);
          this._failCount = 0;
          this._am.update();
          this._updating = true;
        }
      },
      show: function show() {},
      onLoad: function onLoad() {
        if (!cc.sys.isNative) return;
        this._storagePath = (jsb.fileUtils ? jsb.fileUtils.getWritablePath() : "/") + "blackjack-remote-asset";
        cc.log("Storage path for remote asset : " + this._storagePath);
        this.versionCompareHandle = function(versionA, versionB) {
          cc.log("JS Custom Version Compare: version A is " + versionA + ", version B is " + versionB);
          var vA = versionA.split(".");
          var vB = versionB.split(".");
          for (var i = 0; i < vA.length; ++i) {
            var a = parseInt(vA[i]);
            var b = parseInt(vB[i] || 0);
            if (a === b) continue;
            return a - b;
          }
          return vB.length > vA.length ? -1 : 0;
        };
        this._am = new jsb.AssetsManager("", this._storagePath, this.versionCompareHandle);
        cc.sys.ENABLE_GC_FOR_NATIVE_OBJECTS || this._am.retain();
        this._am.setVerifyCallback(function(path, asset) {
          var compressed = asset.compressed;
          var expectedMD5 = asset.md5;
          var relativePath = asset.path;
          var size = asset.size;
          return compressed, true;
        });
        cc.sys.os === cc.sys.OS_ANDROID && this._am.setMaxConcurrentTask(2);
      },
      onDestroy: function onDestroy() {
        if (this._updateListener) {
          cc.eventManager.removeListener(this._updateListener);
          this._updateListener = null;
        }
        this._am && !cc.sys.ENABLE_GC_FOR_NATIVE_OBJECTS && this._am.release();
      }
    });
    cc._RF.pop();
  }, {
    "../alert/TipsAlert": "TipsAlert"
  } ],
  KeyBoard: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "db991dORAJL4Iqi32+GMC3f", "KeyBoard");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var BuyAlert_1 = require("./BuyAlert");
    var Helper_1 = require("../utils/Helper");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var KeyBoard = function(_super) {
      __extends(KeyBoard, _super);
      function KeyBoard() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.numberPad = null;
        _this.mask = null;
        _this.selects = null;
        _this.btnClose = null;
        _this.target = null;
        _this.numbers = [ "7", "8", "9", "4", "5", "6", "1", "2", "3", "0" ];
        return _this;
      }
      KeyBoard.prototype.start = function() {
        var _this = this;
        for (var i = 0; i < this.numberPad.childrenCount; i++) {
          var node = this.numberPad.children[i];
          var button = node.getComponent(cc.Button);
          var clickEventHandler = new cc.Component.EventHandler();
          clickEventHandler.target = this.node;
          clickEventHandler.component = "KeyBoard";
          clickEventHandler.handler = "numberClick";
          clickEventHandler.customEventData = i + "";
          button.clickEvents.push(clickEventHandler);
        }
        this.selects.children.forEach(function(item) {
          var button = item.getComponent(cc.Button);
          var clickEventHandler = new cc.Component.EventHandler();
          clickEventHandler.target = _this.node;
          clickEventHandler.component = "KeyBoard";
          clickEventHandler.handler = "selectClick";
          var label = cc.find("number", item).getComponent(cc.Label);
          clickEventHandler.customEventData = label.string;
          button.clickEvents.push(clickEventHandler);
        });
        this.btnClose.on("click", function() {
          Helper_1.playAlertCloseSound();
          _this.closeKeyBoard();
        });
        this.mask.on("click", function() {
          Helper_1.playAlertCloseSound();
          _this.closeKeyBoard();
        });
      };
      KeyBoard.prototype.selectClick = function(event, customEventData) {
        this.target.number = customEventData;
        this.closeKeyBoard();
        Helper_1.playBtnSound();
      };
      KeyBoard.prototype.numberClick = function(event, customEventData) {
        console.log(customEventData);
        var number = parseInt(customEventData);
        if (this.numbers.length > number) {
          var input = this.numbers[number];
          this.target.input(input);
        } else 10 == number ? this.clearClick() : 11 == number && this.deleteString();
        Helper_1.playBtnSound();
      };
      KeyBoard.prototype.deleteString = function() {
        this.target.delete();
      };
      KeyBoard.prototype.clearClick = function() {
        this.target.number = "";
      };
      KeyBoard.prototype.closeKeyBoard = function() {
        Helper_1.hideKeyBoard();
        BuyAlert_1.default.resumeCloseAction();
      };
      __decorate([ property(cc.Node) ], KeyBoard.prototype, "numberPad", void 0);
      __decorate([ property(cc.Node) ], KeyBoard.prototype, "mask", void 0);
      __decorate([ property(cc.Node) ], KeyBoard.prototype, "selects", void 0);
      __decorate([ property(cc.Node) ], KeyBoard.prototype, "btnClose", void 0);
      KeyBoard = __decorate([ ccclass ], KeyBoard);
      return KeyBoard;
    }(cc.Component);
    exports.default = KeyBoard;
    cc._RF.pop();
  }, {
    "../utils/Helper": "Helper",
    "./BuyAlert": "BuyAlert"
  } ],
  ListItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "0dbc5ANFAVHnIsqaZNEPPsZ", "ListItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ListItemDelegate = function(_super) {
      __extends(ListItemDelegate, _super);
      function ListItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.index = 0;
        _this.model = null;
        return _this;
      }
      ListItemDelegate.prototype.updateItem = function(index, x, y, model) {
        this.node.y = y;
        this.node.x = x;
        this.index = index;
        this.model = model;
      };
      ListItemDelegate = __decorate([ ccclass ], ListItemDelegate);
      return ListItemDelegate;
    }(cc.Component);
    exports.default = ListItemDelegate;
    cc._RF.pop();
  }, {} ],
  ListView: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "4bf27P2iThPeJjnoX29aPXc", "ListView");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ListItemDelegate_1 = require("./ListItemDelegate");
    var AppPageView_1 = require("./AppPageView");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var ViewType;
    (function(ViewType) {
      ViewType[ViewType["SCROLL"] = 0] = "SCROLL";
      ViewType[ViewType["Flip"] = 1] = "Flip";
    })(ViewType || (ViewType = {}));
    var LayoutType;
    (function(LayoutType) {
      LayoutType[LayoutType["GRID"] = 0] = "GRID";
      LayoutType[LayoutType["LIST"] = 1] = "LIST";
    })(LayoutType || (LayoutType = {}));
    var ListView = function(_super) {
      __extends(ListView, _super);
      function ListView() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.itemPrefab = null;
        _this.initCount = 0;
        _this.scrollView = null;
        _this.bufferZone = 0;
        _this.itemHeight = 0;
        _this.viewType = ViewType.SCROLL;
        _this.layoutType = LayoutType.LIST;
        _this.itemWidth = 0;
        _this.spaceY = 0;
        _this.spaceX = 0;
        _this.column = 1;
        _this.offsetStart = 0;
        _this._itemList = [];
        _this.updateTimer = 0;
        _this.updateInterval = .05;
        _this.lastContentPosY = 0;
        _this.dataList = [];
        return _this;
      }
      Object.defineProperty(ListView.prototype, "itemList", {
        get: function() {
          return this._itemList;
        },
        enumerable: true,
        configurable: true
      });
      ListView.prototype.onLoad = function() {
        this.setup();
      };
      ListView.prototype.setup = function() {
        this.preItems();
      };
      ListView.prototype.preItems = function() {
        if (this.layoutType == LayoutType.LIST) {
          var y = .5 * -this.itemHeight;
          for (var i = 0; i < this.initCount; i++) {
            var delegate = cc.instantiate(this.itemPrefab).getComponent(ListItemDelegate_1.default);
            this._itemList.push(delegate);
            delegate.updateItem(i, 0, y, null);
            y -= this.itemHeight + this.spaceY;
            this.node.addChild(delegate.node);
            delegate.node.active = false;
          }
        } else for (var i = 0; i < this.initCount; i++) {
          var delegate = cc.instantiate(this.itemPrefab).getComponent(ListItemDelegate_1.default);
          this._itemList.push(delegate);
          var row = Math.floor(i / this.column);
          var col = Math.floor(i % this.column) + 1;
          var y = -this.itemHeight / 2 - (row - 1) * this.spaceY - row * this.itemHeight;
          var x = this.itemWidth / 2 + (col - 1) * (this.spaceX + this.itemWidth);
          delegate.updateItem(i, x, y, null);
          delegate.node.active = false;
          this.node.addChild(delegate.node);
        }
      };
      ListView.prototype.reloadList = function() {
        if (this.layoutType == LayoutType.LIST) {
          var y = .5 * -this.itemHeight;
          for (var i = 0; i < this.initCount; i++) {
            var delegate = this.itemList[i];
            if (i < this.dataList.length) {
              delegate.node.active = true;
              delegate.updateItem(i, 0, y, this.dataList[i]);
            } else delegate.node.active = false;
            y -= this.itemHeight + this.spaceY;
          }
          this.scrollView.content.height = this.dataList.length * (this.itemHeight + this.spaceY);
        } else {
          for (var i = 0; i < this._itemList.length; i++) {
            var delegate = this._itemList[i];
            var row = Math.floor(i / this.column);
            var col = Math.floor(i % this.column) + 1;
            var y = -this.itemHeight / 2 - (row - 1) * this.spaceY - row * this.itemHeight;
            var x = this.itemWidth / 2 + (col - 1) * (this.spaceX + this.itemWidth);
            if (i < this.dataList.length) {
              delegate.node.active = true;
              delegate.updateItem(i, x, y, this.dataList[i]);
            } else delegate.node.active = false;
          }
          var rowSize = Math.ceil(this.dataList.length / this.column);
          if (this.viewType == ViewType.Flip) {
            var pageRowSize = (this.scrollView.node.height + this.spaceY) / (this.itemHeight + this.spaceY);
            var page = Math.ceil(rowSize / pageRowSize);
            var appPageView = this.scrollView.node.getComponent(AppPageView_1.default);
            appPageView && (appPageView.pageSize = page);
          } else this.scrollView.content.height = rowSize * (this.itemHeight + this.spaceY) - this.spaceY;
        }
      };
      ListView.prototype.update = function(dt) {
        this.updateTimer += dt;
        if (this.updateTimer < this.updateInterval) return;
        this.updateTimer = 0;
        var items = this._itemList;
        var buffer = this.bufferZone;
        var isDown = this.node.y < this.lastContentPosY;
        var isUp = this.node.y > this.lastContentPosY;
        var curItemCount = this._itemList.length;
        var offset = 0;
        offset = this.layoutType == LayoutType.LIST ? (this.itemHeight + this.spaceY) * curItemCount - this.spaceY : (this.itemHeight + this.spaceY) * Math.floor(curItemCount / this.column);
        for (var i = 0; i < curItemCount; ++i) {
          var item = items[i];
          var itemNode = item.node;
          var viewPos = this.getPositionInView(itemNode);
          if (isDown) {
            if (viewPos.y + this.offsetStart < -buffer && itemNode.y + offset < 0) {
              var newIdx = item.index - curItemCount;
              console.log(newIdx);
              if (newIdx < this.dataList.length) {
                var newInfo = this.dataList[newIdx];
                if (this.layoutType == LayoutType.LIST) item.updateItem(newIdx, 0, itemNode.y + offset, newInfo); else {
                  var row = Math.floor(newIdx / this.column);
                  var col = Math.floor(newIdx % this.column) + 1;
                  var y = -this.itemHeight / 2 - (row - 1) * this.spaceY - row * this.itemHeight;
                  var x = this.itemWidth / 2 + (col - 1) * (this.spaceX + this.itemWidth);
                  item.updateItem(newIdx, x, y, newInfo);
                }
              }
            }
          } else if (isUp && viewPos.y - this.offsetStart > buffer && itemNode.y - offset > -this.node.height) {
            var newIdx = item.index + curItemCount;
            console.log(newIdx);
            if (newIdx < this.dataList.length) {
              var newInfo = this.dataList[newIdx];
              if (this.layoutType == LayoutType.LIST) item.updateItem(newIdx, 0, itemNode.y - offset, newInfo); else {
                var row = Math.floor(newIdx / this.column);
                var col = Math.floor(newIdx % this.column) + 1;
                var y = -this.itemHeight / 2 - (row - 1) * this.spaceY - row * this.itemHeight;
                var x = this.itemWidth / 2 + (col - 1) * (this.spaceX + this.itemWidth);
                item.updateItem(newIdx, x, y, newInfo);
              }
            }
          }
        }
        this.lastContentPosY = this.node.y;
      };
      ListView.prototype.getPositionInView = function(item) {
        var worldPos = item.parent.convertToWorldSpaceAR(item.position);
        var viewPos = this.scrollView.node.convertToNodeSpaceAR(worldPos);
        return viewPos;
      };
      __decorate([ property(cc.Prefab) ], ListView.prototype, "itemPrefab", void 0);
      __decorate([ property ], ListView.prototype, "initCount", void 0);
      __decorate([ property(cc.ScrollView) ], ListView.prototype, "scrollView", void 0);
      __decorate([ property ], ListView.prototype, "bufferZone", void 0);
      __decorate([ property ], ListView.prototype, "itemHeight", void 0);
      __decorate([ property({
        type: cc.Enum(ViewType)
      }) ], ListView.prototype, "viewType", void 0);
      __decorate([ property({
        type: cc.Enum(LayoutType)
      }) ], ListView.prototype, "layoutType", void 0);
      __decorate([ property ], ListView.prototype, "itemWidth", void 0);
      __decorate([ property ], ListView.prototype, "spaceY", void 0);
      __decorate([ property ], ListView.prototype, "spaceX", void 0);
      __decorate([ property ], ListView.prototype, "column", void 0);
      __decorate([ property ], ListView.prototype, "offsetStart", void 0);
      ListView = __decorate([ ccclass ], ListView);
      return ListView;
    }(cc.Component);
    exports.default = ListView;
    cc._RF.pop();
  }, {
    "./AppPageView": "AppPageView",
    "./ListItemDelegate": "ListItemDelegate"
  } ],
  LoadMore: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "820680S9iVLYo7FGnSciDi2", "LoadMore");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var LoadMore = function(_super) {
      __extends(LoadMore, _super);
      function LoadMore() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.loading = null;
        return _this;
      }
      LoadMore.prototype.onScroll = function(scrollView, event) {
        var offsetY = Math.abs(scrollView.getMaxScrollOffset().y - scrollView.getScrollOffset().y);
        if (event == cc.ScrollView.EventType.BOUNCE_BOTTOM && offsetY > 150) {
          console.log("上拉加载");
          this.loading.runAction(cc.fadeIn(.15));
          this.node.emit("LoadMore");
        }
      };
      LoadMore.prototype.start = function() {};
      __decorate([ property(cc.Node) ], LoadMore.prototype, "loading", void 0);
      LoadMore = __decorate([ ccclass ], LoadMore);
      return LoadMore;
    }(cc.Component);
    exports.default = LoadMore;
    cc._RF.pop();
  }, {} ],
  Loading: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "91c97qyfUZJwZvIHzAtfGMB", "Loading");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ApiNet_1 = require("./utils/ApiNet");
    var config_1 = require("./config");
    var Helper_1 = require("./utils/Helper");
    var MenuItemDelegate_1 = require("./menus/MenuItemDelegate");
    var AppData_1 = require("./AppData");
    var NodeCaches_1 = require("./NodeCaches");
    var RecycleView_1 = require("./widget/RecycleView");
    var TipsAlert_1 = require("./alert/TipsAlert");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var Loading = function(_super) {
      __extends(Loading, _super);
      function Loading() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.progressBar = null;
        _this.football = null;
        _this.oddsList = null;
        _this.bgAudio = null;
        _this.btnAudio = null;
        _this.alertOpenAudio = null;
        _this.menuSelectAudio = null;
        _this.alertCloseAudio = null;
        _this.selectedAudio = null;
        _this.loading = true;
        _this.checkUpdated = false;
        return _this;
      }
      Loading.prototype.start = function() {
        var _this = this;
        if (cc.sys.isNative) {
          if (cc.sys.OS_ANDROID === cc.sys.os) {
            config_1.default.appGameInfo = null;
            config_1.default.appUserInfo = null;
            config_1.default.userData = null;
          } else {
            config_1.default.HOST_URL = config_1.default.appGameInfo.gameHost;
            config_1.default.IMG_URL = "";
          }
          this.hotUpdate = cc.find("update").getComponent("HotUpdate");
          this.hotUpdate.checkUpdate();
          this.hotUpdate.callback = function(needUpdate) {
            _this.updateCheck(needUpdate);
          };
          this.hotUpdate.progressCallback = function(progress) {
            _this.progressBar.progress = progress;
            _this.football.x = 1048 * _this.progressBar.progress;
          };
        } else this.initLoad();
      };
      Loading.prototype.initLoad = function() {
        var _this = this;
        this.loadPrefabs();
        cc.director.preloadScene("main", function(error) {
          _this.waiting();
        });
      };
      Loading.prototype.updateCheck = function(needUpdate) {
        var _this = this;
        cc.log("是否需要更新----" + (needUpdate ? "是" : "否"));
        needUpdate ? TipsAlert_1.default.show(.3, "提示", "检测到新版本，需要更新", function() {
          _this.hotUpdate.hotUpdate();
        }) : this.initLoad();
      };
      Loading.prototype.waiting = function() {
        var _this = this;
        if (config_1.default) {
          var config = config_1.default;
          if (cc.sys.OS_ANDROID === cc.sys.os) try {
            this.setupConfig(config);
          } catch (e) {
            TipsAlert_1.default.show(.3, "错误", "获取平台信息失败。", function() {
              Helper_1.exitGame();
            }, "确定");
          }
          null == config.appUserInfo && null == config.appGameInfo && this.loading ? this.scheduleOnce(function() {
            _this.waiting();
          }, .3) : this.requestLogin();
        }
      };
      Loading.prototype.setupConfig = function(config) {
        var gameInfo = jsb.reflection.callStaticMethod("org/cocos2dx/javascript/AppActivity", "getGameInfo", "()Ljava/lang/String;");
        var userInfo = jsb.reflection.callStaticMethod("org/cocos2dx/javascript/AppActivity", "getUserInfo", "()Ljava/lang/String;");
        config.appUserInfo = JSON.parse(userInfo);
        config.appGameInfo = JSON.parse(gameInfo);
        config.appGameInfo.gameHost = config.appGameInfo.gameHost.replace("\\/g", "");
        config.HOST_URL = config.appGameInfo.gameHost;
        config.IMG_URL = "";
      };
      Loading.prototype.requestLogin = function() {
        var _this = this;
        ApiNet_1.default.post("/user/passport/login", {
          uuid: config_1.default.appUserInfo.uuid,
          userName: config_1.default.appUserInfo.userName,
          phone: config_1.default.appUserInfo.phone,
          cpid: config_1.default.appUserInfo.cpid,
          location: config_1.default.appUserInfo.location,
          sexMode: config_1.default.appUserInfo.sexModel,
          imei: config_1.default.appUserInfo.imei,
          gamekey: config_1.default.appGameInfo.gameKey,
          token: config_1.default.appUserInfo.token
        }).then(function(res) {
          console.log(res);
          if (res && 0 == res.code) {
            config_1.default.userData = res.data;
            Helper_1.loadGameData().then(function(res) {
              _this.setupAppMenus();
              cc.director.loadScene("main");
            });
          } else TipsAlert_1.default.show(.3, "错误", "链接服务器失败，请稍后再试!", function() {
            Helper_1.exitGame();
          }, "确定");
        }).catch(function(error) {
          console.log(error);
          TipsAlert_1.default.show(.3, "错误", "链接服务器失败，请稍后再试!", function() {
            Helper_1.exitGame();
          }, "确定");
        });
      };
      Loading.prototype.loadPrefabs = function() {
        var _this = this;
        cc.loader.loadResDir("prefabs", cc.Asset, function(count, total, item) {
          var progress = count / total * .8;
          if (_this.progressBar) {
            _this.progressBar.progress = progress;
            _this.football.x = 1048 * progress;
          }
        }, function(error, asset) {
          if (error) console.log(error); else {
            console.log(asset);
            asset.forEach(function(item) {
              NodeCaches_1.default.prefabs.set(item._name, item);
            });
            console.log(NodeCaches_1.default.prefabs);
            _this.loadAudio();
          }
        });
      };
      Loading.prototype.loadAudio = function() {
        AppData_1.default.sound.bg = this.bgAudio;
        AppData_1.default.sound.open = this.alertOpenAudio;
        AppData_1.default.sound.close = this.alertCloseAudio;
        AppData_1.default.sound.btn = this.btnAudio;
        AppData_1.default.sound.select = this.selectedAudio;
        AppData_1.default.sound.menu = this.menuSelectAudio;
        this.preItems();
      };
      Loading.prototype.setupMatches = function(mathRes) {};
      Loading.prototype.setupGames = function(mathRes) {};
      Loading.prototype.setupAppMenus = function() {
        for (var i = 0; i < AppData_1.default.matches.length; i++) {
          var menuItem = null;
          menuItem = NodeCaches_1.default.menuItemCaches.size() > 0 ? NodeCaches_1.default.menuItemCaches.get() : cc.instantiate(NodeCaches_1.default.prefabs.get("menuItem"));
          var menuModel = AppData_1.default.matches[i];
          var component = menuItem.getComponent(MenuItemDelegate_1.default);
          component.item = menuModel;
          component.type = "menu";
          NodeCaches_1.default.menusItemCaches.push(menuItem);
          if (this.progressBar) {
            this.progressBar.progress = .8 + .2 * (i + 1) / AppData_1.default.matches.length;
            this.football.x = 1048 * this.progressBar.progress;
          }
        }
      };
      Loading.prototype.preItems = function() {
        this.oddsList.preItems();
        NodeCaches_1.default.sectionLists.put(this.oddsList.scrollView.node);
        for (var i = 0; i < AppData_1.default.rules.length; i++) {
          var menuItem = null;
          menuItem = NodeCaches_1.default.menuItemCaches.size() > 0 ? NodeCaches_1.default.menuItemCaches.get() : cc.instantiate(NodeCaches_1.default.prefabs.get("menuItem"));
          var component = menuItem.getComponent(MenuItemDelegate_1.default);
          component.item = AppData_1.default.rules[i];
          component.type = "type";
          NodeCaches_1.default.typeMenuItemCaches.push(menuItem);
        }
        this.loading = false;
      };
      __decorate([ property(cc.ProgressBar) ], Loading.prototype, "progressBar", void 0);
      __decorate([ property(cc.Node) ], Loading.prototype, "football", void 0);
      __decorate([ property(RecycleView_1.default) ], Loading.prototype, "oddsList", void 0);
      __decorate([ property(cc.AudioClip) ], Loading.prototype, "bgAudio", void 0);
      __decorate([ property(cc.AudioClip) ], Loading.prototype, "btnAudio", void 0);
      __decorate([ property(cc.AudioClip) ], Loading.prototype, "alertOpenAudio", void 0);
      __decorate([ property(cc.AudioClip) ], Loading.prototype, "menuSelectAudio", void 0);
      __decorate([ property(cc.AudioClip) ], Loading.prototype, "alertCloseAudio", void 0);
      __decorate([ property(cc.AudioClip) ], Loading.prototype, "selectedAudio", void 0);
      Loading = __decorate([ ccclass ], Loading);
      return Loading;
    }(cc.Component);
    exports.default = Loading;
    cc._RF.pop();
  }, {
    "./AppData": "AppData",
    "./NodeCaches": "NodeCaches",
    "./alert/TipsAlert": "TipsAlert",
    "./config": "config",
    "./menus/MenuItemDelegate": "MenuItemDelegate",
    "./utils/ApiNet": "ApiNet",
    "./utils/Helper": "Helper",
    "./widget/RecycleView": "RecycleView"
  } ],
  LogItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "6da62neh6RM/Yuw7k4ZOZuF", "LogItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ListItemDelegate_1 = require("../widget/ListItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var LogItemDelegate = function(_super) {
      __extends(LogItemDelegate, _super);
      function LogItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.timeLabel = null;
        _this.infoLabel = null;
        _this.moneyLabel = null;
        _this.resultLabel = null;
        _this._item = null;
        return _this;
      }
      Object.defineProperty(LogItemDelegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      LogItemDelegate.prototype.updateItem = function(index, x, y, model) {
        _super.prototype.updateItem.call(this, index, x, y, model);
        model && (this.item = model);
      };
      LogItemDelegate.prototype.onItemClick = function() {};
      LogItemDelegate.prototype.updateData = function() {
        var dayjs = window["dayjs"];
        if (this.item) {
          var date = dayjs(1e3 * this.item.create_time);
          this.timeLabel.string = date.format("MM月DD日\nHH:mm");
          this.infoLabel.string = this.item.teams[0].name + "VS" + this.item.teams[1].name + "\n" + this.item.bet_target.rules_name + " \n赔率:" + this.item.odds;
          this.moneyLabel.string = this.item.money + "\n" + this.item.bet_target.target;
          var status = parseInt(this.item.play.status);
          if (100 != status) {
            var money = parseFloat(this.item.odds) * parseFloat(this.item.money);
            this.resultLabel.string = "等待结算\n可赢" + money.toFixed(2);
            this.resultLabel.node.color = cc.hexToColor("#f0a51e");
          } else {
            var win_money = this.item.win_money;
            if (win_money > 0) {
              this.resultLabel.string = "胜+" + win_money;
              this.resultLabel.node.color = cc.hexToColor("#f0380c");
            } else {
              this.resultLabel.string = "负-" + parseFloat(this.item.money).toFixed(2);
              this.resultLabel.node.color = cc.hexToColor("#45f02b");
            }
          }
        }
      };
      __decorate([ property(cc.Label) ], LogItemDelegate.prototype, "timeLabel", void 0);
      __decorate([ property(cc.Label) ], LogItemDelegate.prototype, "infoLabel", void 0);
      __decorate([ property(cc.Label) ], LogItemDelegate.prototype, "moneyLabel", void 0);
      __decorate([ property(cc.Label) ], LogItemDelegate.prototype, "resultLabel", void 0);
      LogItemDelegate = __decorate([ ccclass ], LogItemDelegate);
      return LogItemDelegate;
    }(ListItemDelegate_1.default);
    exports.default = LogItemDelegate;
    cc._RF.pop();
  }, {
    "../widget/ListItemDelegate": "ListItemDelegate"
  } ],
  LogListAlert: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "869e5Ny/RRJyIgPJrzHbrAL", "LogListAlert");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ApiNet_1 = require("../utils/ApiNet");
    var ListView_1 = require("../widget/ListView");
    var Helper_1 = require("../utils/Helper");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var LogListAlert = function() {
      function LogListAlert() {}
      LogListAlert_1 = LogListAlert;
      LogListAlert.preItems = function() {};
      LogListAlert.show = function(speed) {
        var _this = this;
        this._speed = speed || this._speed;
        if (null != this._alert) {
          this._alert.setLocalZOrder(999);
          this.startFadeIn();
          return;
        }
        this._speed = speed || this._speed;
        cc.loader.loadRes("prefabs/LogListAlert", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          _this._alert = cc.instantiate(prefab);
          _this._mask = cc.find("mask", _this._alert);
          _this._bg = cc.find("alertBg", _this._alert);
          var cbFadeOut = cc.callFunc(_this.onFadeOutFinish, _this);
          var cbFadeIn = cc.callFunc(_this.onFadeInFinish, _this);
          _this.actionFadeIn = cc.sequence(cc.moveBy(_this._speed, cc.p(-_this._bg.width)), cbFadeIn);
          _this.actionFadeOut = cc.sequence(cc.moveBy(_this._speed, cc.p(_this._bg.width)), cbFadeOut);
          _this.scrollView = cc.find("alertBg/ScrollView", _this._alert);
          _this._content = cc.find("alertBg/ScrollView/view/list", _this._alert);
          _this._listView = _this._content.getComponent(ListView_1.default);
          _this._pageLoading = cc.find("alertBg/loading", _this._alert);
          console.log("--------初始化listView");
          _this._cancelButton = cc.find("alertBg/btnCancel", _this._alert);
          _this._cancelButton.on("click", _this.onButtonClicked, _this);
          _this._alert.parent = cc.find("Canvas");
          _this.scrollView.on("LoadMore", function() {
            _this.page++;
            _this.loadLogs();
          });
          _this.startFadeIn();
        });
      };
      LogListAlert.onButtonClicked = function(event) {
        "enterButton" == event.target.name && this._enterAction && this._enterAction();
        this.startFadeOut();
      };
      LogListAlert.startFadeOut = function() {
        Helper_1.playAlertCloseSound();
        cc.eventManager.pauseTarget(this._alert, true);
        this._mask.runAction(cc.fadeOut(this._speed));
        this._alert.runAction(this.actionFadeOut);
      };
      LogListAlert.loadLogs = function() {
        if (this.hasMore) ApiNet_1.default.post("/user/my/bet", {
          page: this.page
        }).then(function(res) {
          console.log(res);
          0 == res.code && LogListAlert_1.setupLogData(res.data);
        }); else {
          cc.find("alertBg/ScrollView/loading", this._alert).runAction(cc.fadeOut(.15));
          this._pageLoading.runAction(cc.fadeOut(.15));
        }
      };
      LogListAlert.setupLogData = function(data) {
        if (data.length < 10) {
          this.hasMore = false;
          this.page > 1 && this.setupFooter();
        }
        if (1 == this.page) {
          this._listView.dataList = data;
          this._pageLoading.runAction(cc.fadeOut(.15));
        } else this._listView.dataList = this._listView.dataList.concat(data);
        this._listView.reloadList();
        1 == this.page && this.scrollView.getComponent(cc.ScrollView).scrollToTop(0);
        cc.find("alertBg/ScrollView/loading", this._alert).runAction(cc.fadeOut(.15));
      };
      LogListAlert.setupFooter = function() {
        var _this = this;
        cc.loader.loadRes("prefabs/footer", cc.Prefab, function(error, resource) {
          if (error) {
            cc.error(error);
            return;
          }
          var footer = cc.instantiate(resource);
          footer.name = "loadMoreFooter";
          footer.parent = _this._content;
          footer.y = -_this._content.height - .5 * footer.height;
        });
      };
      LogListAlert.onFadeInFinish = function() {
        this._alert.resumeSystemEvents(true);
        this.page = 1;
        this.loadLogs();
      };
      LogListAlert.onFadeOutFinish = function() {};
      LogListAlert.onDestory = function() {
        this._alert.destroy();
        this._enterAction = null;
        this._alert = null;
        this._titleLabel = null;
        this._cancelButton = null;
        this._enterButton = null;
        this._speed = .3;
      };
      LogListAlert.startFadeIn = function() {
        Helper_1.playAlertOpenSound();
        this.hasMore = true;
        this._alert.pauseSystemEvents(true);
        this._alert.position = cc.p(0, 0);
        this._mask.opacity = 0;
        this._mask.runAction(cc.fadeTo(this._speed, 156));
        this._alert.runAction(this.actionFadeIn);
      };
      LogListAlert._alert = null;
      LogListAlert._mask = null;
      LogListAlert._titleLabel = null;
      LogListAlert._cancelButton = null;
      LogListAlert._enterButton = null;
      LogListAlert._speed = .3;
      LogListAlert.actionFadeIn = null;
      LogListAlert._enterAction = null;
      LogListAlert.actionFadeOut = null;
      LogListAlert._bg = null;
      LogListAlert.page = 1;
      LogListAlert._content = null;
      LogListAlert.logNodePool = new cc.NodePool();
      LogListAlert.prefab = null;
      LogListAlert.scrollView = null;
      LogListAlert.hasMore = true;
      LogListAlert._listView = null;
      LogListAlert = LogListAlert_1 = __decorate([ ccclass ], LogListAlert);
      return LogListAlert;
      var LogListAlert_1;
    }();
    exports.default = LogListAlert;
    cc._RF.pop();
  }, {
    "../utils/ApiNet": "ApiNet",
    "../utils/Helper": "Helper",
    "../widget/ListView": "ListView"
  } ],
  MenuItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "6a4b5WOJlJNvqniZ0SByFmW", "MenuItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var Helper_1 = require("../utils/Helper");
    var NodeCaches_1 = require("../NodeCaches");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var MenuItemDelegate = function(_super) {
      __extends(MenuItemDelegate, _super);
      function MenuItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.label = null;
        _this.background = null;
        _this.icon = null;
        _this._item = null;
        _this.scrollStartY = 0;
        _this.scrollEndY = 0;
        _this.selectedBackgroundFrame = null;
        _this.backgroundFrame = null;
        _this.iconSelectedFrame = null;
        _this.iconFrame = null;
        _this.type = "menu";
        return _this;
      }
      Object.defineProperty(MenuItemDelegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.loadFrames();
        },
        enumerable: true,
        configurable: true
      });
      MenuItemDelegate.prototype.onMenuItemClick = function() {
        if (!this.item.selected) {
          this.updateOther();
          this.item.selected = true;
          "menu" == this.type ? NodeCaches_1.default.lastMatchMenu = this : NodeCaches_1.default.lastSelectTypeMenu = this;
          this.refresh();
        }
        Helper_1.playMenuSelectSound();
        console.log(this.item);
        this.item.action && this.item.action(this.item, this);
      };
      MenuItemDelegate.prototype.updateOther = function() {
        if ("menu" == this.type) {
          if (NodeCaches_1.default.lastMatchMenu) {
            var component = NodeCaches_1.default.lastMatchMenu;
            component.item.selected = false;
            component.refresh();
          }
        } else if (NodeCaches_1.default.lastSelectTypeMenu) {
          var component = NodeCaches_1.default.lastSelectTypeMenu;
          component.item.selected = false;
          component.refresh();
        }
      };
      MenuItemDelegate.prototype.loadFrames = function() {
        var _this = this;
        this.label.string = this.item.name;
        Promise.all([ Helper_1.loadSpriteFrame("menus/" + this.item.logo), Helper_1.loadSpriteFrame("menus/" + this.item.logo_hover) ]).then(function(items) {
          _this.iconFrame = items[0]["img"];
          _this.iconSelectedFrame = items[1]["img"];
          _this.refresh();
        }).catch(function(error) {
          console.log(error);
          _this.refresh();
        });
      };
      MenuItemDelegate.prototype.refresh = function() {
        this.background.spriteFrame = this.item.selected ? this.selectedBackgroundFrame : this.backgroundFrame;
        this.icon.spriteFrame = this.item.selected ? this.iconSelectedFrame : this.iconFrame;
      };
      __decorate([ property(cc.Label) ], MenuItemDelegate.prototype, "label", void 0);
      __decorate([ property(cc.Sprite) ], MenuItemDelegate.prototype, "background", void 0);
      __decorate([ property(cc.Sprite) ], MenuItemDelegate.prototype, "icon", void 0);
      __decorate([ property(cc.SpriteFrame) ], MenuItemDelegate.prototype, "selectedBackgroundFrame", void 0);
      __decorate([ property(cc.SpriteFrame) ], MenuItemDelegate.prototype, "backgroundFrame", void 0);
      MenuItemDelegate = __decorate([ ccclass ], MenuItemDelegate);
      return MenuItemDelegate;
    }(cc.Component);
    exports.default = MenuItemDelegate;
    cc._RF.pop();
  }, {
    "../NodeCaches": "NodeCaches",
    "../utils/Helper": "Helper"
  } ],
  MenuListScrollDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "c9ef3yx6jxFgZYmmCcZ1Egk", "MenuListScrollDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var MenuListScrollDelegate = function(_super) {
      __extends(MenuListScrollDelegate, _super);
      function MenuListScrollDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.arrowUp = null;
        _this.arrowDown = null;
        _this.scrollView = null;
        _this.list = null;
        return _this;
      }
      MenuListScrollDelegate.prototype.onLoad = function() {
        var _this = this;
        this.arrowDown.node.on("click", function() {
          _this.scrollView.scrollToBottom(.3, true);
        });
        this.arrowUp.node.on("click", function() {
          _this.scrollView.scrollToTop(.3, true);
        });
        this.list = cc.find("view/menus", this.node).getComponent(cc.Layout);
      };
      MenuListScrollDelegate.prototype.onScroll = function(scrollView, type) {};
      __decorate([ property(cc.Sprite) ], MenuListScrollDelegate.prototype, "arrowUp", void 0);
      __decorate([ property(cc.Sprite) ], MenuListScrollDelegate.prototype, "arrowDown", void 0);
      __decorate([ property(cc.ScrollView) ], MenuListScrollDelegate.prototype, "scrollView", void 0);
      MenuListScrollDelegate = __decorate([ ccclass ], MenuListScrollDelegate);
      return MenuListScrollDelegate;
    }(cc.Component);
    exports.default = MenuListScrollDelegate;
    cc._RF.pop();
  }, {} ],
  MenuModel: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "73736RQX0xA3rRS8s2u5ElE", "MenuModel");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var MenuModel = function() {
      function MenuModel() {
        this.name = "全部比赛";
        this.iconString = "";
        this.iconSelectedString = "";
        this.selected = false;
        this.tag = "";
        this.index = 0;
        this.action = null;
        this.type = "";
      }
      MenuModel = __decorate([ ccclass ], MenuModel);
      return MenuModel;
    }();
    exports.default = MenuModel;
    cc._RF.pop();
  }, {} ],
  MenuScrollDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "1f533KppxFPuJwkzinCbNJn", "MenuScrollDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var MenuScrollDelegate = function(_super) {
      __extends(MenuScrollDelegate, _super);
      function MenuScrollDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.arrowUp = null;
        _this.arrowDown = null;
        return _this;
      }
      MenuScrollDelegate.prototype.onScroll = function(scrollView, event) {
        var number = Math.floor(scrollView.getScrollOffset().y);
        console.log(number);
        this.arrowUp.active = number > 0;
        this.arrowDown.active = number < Math.floor(Math.abs(scrollView.getMaxScrollOffset().y));
      };
      __decorate([ property(cc.Node) ], MenuScrollDelegate.prototype, "arrowUp", void 0);
      __decorate([ property(cc.Node) ], MenuScrollDelegate.prototype, "arrowDown", void 0);
      MenuScrollDelegate = __decorate([ ccclass ], MenuScrollDelegate);
      return MenuScrollDelegate;
    }(cc.Component);
    exports.default = MenuScrollDelegate;
    cc._RF.pop();
  }, {} ],
  NodeCaches: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "a8071IojS1B1I1QjxVzUu7l", "NodeCaches");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var nodeCaches = {
      moveItem: null,
      menuItemCaches: new cc.NodePool(),
      gameItemCaches: new cc.NodePool(),
      oddItemCaches: new cc.NodePool(),
      prefabs: new Map(),
      sectionItemCaches: [],
      menusItemCaches: [],
      typeMenuItemCaches: [],
      lastSelectTypeMenu: null,
      lastMatchMenu: null,
      sectionGridItems: new cc.NodePool(),
      sectionLists: new cc.NodePool()
    };
    window["nodeCaches"] = nodeCaches;
    exports.default = nodeCaches;
    cc._RF.pop();
  }, {} ],
  NoticeBar: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "53b81Dw0yJDgqbtYAkoJ1t1", "NoticeBar");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ApiNet_1 = require("../utils/ApiNet");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var NoticeBar = function(_super) {
      __extends(NoticeBar, _super);
      function NoticeBar() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.label = null;
        _this.is_runing = false;
        _this.message = "";
        return _this;
      }
      NoticeBar.prototype.onLoad = function() {
        this.label.string = "";
        this.runLoop();
      };
      NoticeBar.prototype.runLoop = function() {
        var _this = this;
        var width = 700;
        this.label.string = this.message;
        this.label.node.x = 350;
        var time = (this.label.node.width + width) / 30 * .3;
        var action = cc.moveBy(time, cc.p(-(this.label.node.width + width)));
        var finished = cc.callFunc(function() {
          _this.runLoop();
        }, this);
        this.label.node.runAction(cc.sequence(action, finished));
      };
      NoticeBar.prototype.updateMessage = function() {
        var _this = this;
        ApiNet_1.default.post("/index/common/notice", {}).then(function(res) {
          console.log(res);
          var data = res.data;
          var content = data.map(function(item) {
            return item.content;
          }).join(" ");
          _this.message = content;
          console.log(_this.label.node.width);
        }).catch(function(error) {});
      };
      NoticeBar.prototype.start = function() {
        this.schedule(this.updateMessage, 5, cc.macro.REPEAT_FOREVER, .3);
      };
      __decorate([ property(cc.Label) ], NoticeBar.prototype, "label", void 0);
      NoticeBar = __decorate([ ccclass ], NoticeBar);
      return NoticeBar;
    }(cc.Component);
    exports.default = NoticeBar;
    cc._RF.pop();
  }, {
    "../utils/ApiNet": "ApiNet"
  } ],
  NumberInput: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "31ebb7sLT1Jqp2aj8x1wncD", "NumberInput");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var NodeCaches_1 = require("../NodeCaches");
    var AppGame_1 = require("../AppGame");
    var BuyItemDelegate_1 = require("./BuyItemDelegate");
    var BuyAlert_1 = require("./BuyAlert");
    var Helper_1 = require("../utils/Helper");
    var KeyBoard_1 = require("./KeyBoard");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var NumberInput = function(_super) {
      __extends(NumberInput, _super);
      function NumberInput() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.inputNumber = null;
        _this.placeHolder = null;
        _this.max = 1e4;
        _this.min = 100;
        _this._number = "";
        return _this;
      }
      Object.defineProperty(NumberInput.prototype, "number", {
        get: function() {
          return this._number;
        },
        set: function(value) {
          this._number = value;
          this.updateFrame();
        },
        enumerable: true,
        configurable: true
      });
      NumberInput.prototype.input = function(key) {
        if ("" == this.number && "." == key || "" == this.number && "00" == key || "0" == this.number && "0" == key || "0" == this.number && "00" == key) return;
        if (-1 != this.number.indexOf(".") && "." == key) return;
        if (-1 != this.number.indexOf(".")) {
          var index = this.number.indexOf(".");
          if (this.number.length > index + 2) return;
        }
        this.number = this.number + key;
        parseInt(this.number) > 1e4 && (this.number = "10000");
      };
      NumberInput.prototype.delete = function() {
        this.number = this.number.substr(0, this.number.length - 1);
      };
      NumberInput.prototype.updateFrame = function() {
        this.inputNumber.string = this._number;
        if ("" != this._number) {
          this.placeHolder.node.active = false;
          this.inputNumber.node.active = true;
        } else {
          this.placeHolder.node.active = true;
          this.inputNumber.node.active = false;
        }
        this.node.emit("onNumberChange", this.number);
      };
      NumberInput.prototype.onOpenKeyBoard = function() {
        var component = this.node.parent.parent.getComponent(BuyItemDelegate_1.default);
        if (!AppGame_1.default.numberKeyBoard) {
          var keyBord = NodeCaches_1.default.prefabs.get("keyboard");
          keyBord && (AppGame_1.default.numberKeyBoard = cc.instantiate(keyBord));
        }
        var x = 0;
        var y = 180;
        console.log(component.node.x);
        x = component.node.parent.childrenCount - 1 == component.index ? component.node.x - 230 - 339 : component.node.x + 230 + 339;
        cc.find("content", AppGame_1.default.numberKeyBoard).getComponent(KeyBoard_1.default).target = this;
        AppGame_1.default.numberKeyBoard.x = x;
        AppGame_1.default.numberKeyBoard.y = y;
        AppGame_1.default.numberKeyBoard.parent = BuyAlert_1.default._keyboardContainer;
        Helper_1.showKeyBoard();
        Helper_1.playAlertOpenSound();
      };
      NumberInput.prototype.start = function() {};
      __decorate([ property(cc.Label) ], NumberInput.prototype, "inputNumber", void 0);
      __decorate([ property(cc.Label) ], NumberInput.prototype, "placeHolder", void 0);
      __decorate([ property ], NumberInput.prototype, "number", null);
      NumberInput = __decorate([ ccclass ], NumberInput);
      return NumberInput;
    }(cc.Component);
    exports.default = NumberInput;
    cc._RF.pop();
  }, {
    "../AppGame": "AppGame",
    "../NodeCaches": "NodeCaches",
    "../utils/Helper": "Helper",
    "./BuyAlert": "BuyAlert",
    "./BuyItemDelegate": "BuyItemDelegate",
    "./KeyBoard": "KeyBoard"
  } ],
  RecycleItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "abc99mACrFANKNpTq5La9GO", "RecycleItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var RecycleItemDelegate = function(_super) {
      __extends(RecycleItemDelegate, _super);
      function RecycleItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.index = 0;
        _this.model = null;
        return _this;
      }
      RecycleItemDelegate.prototype.updateItem = function(index, x, y, model) {
        this.node.y = y;
        this.node.x = x;
        this.index = index;
        this.model = model;
      };
      RecycleItemDelegate = __decorate([ ccclass ], RecycleItemDelegate);
      return RecycleItemDelegate;
    }(cc.Component);
    exports.default = RecycleItemDelegate;
    cc._RF.pop();
  }, {} ],
  RecycleView: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "d6223KB3ZtBmL59C01MYMbn", "RecycleView");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var SectionRowDelegate_1 = require("../games/SectionRowDelegate");
    var NodeCaches_1 = require("../NodeCaches");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var RecycleView = function(_super) {
      __extends(RecycleView, _super);
      function RecycleView() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.recycleItems = [];
        _this.scrollView = null;
        _this.itemHeight = 72;
        _this.spaceY = 30;
        _this.layoutRow = 11;
        _this.offsetStart = 0;
        _this.scrollItems = [];
        _this._dataList = [];
        _this.updateTimer = 0;
        _this.updateInterval = .2;
        _this.bufferZone = 350;
        _this.lastContentPosY = 0;
        return _this;
      }
      Object.defineProperty(RecycleView.prototype, "dataList", {
        get: function() {
          return this._dataList;
        },
        set: function(value) {
          this._dataList = value;
        },
        enumerable: true,
        configurable: true
      });
      RecycleView.prototype.preItems = function() {
        var y = .5 * -this.itemHeight;
        NodeCaches_1.default.moveItem = cc.instantiate(NodeCaches_1.default.prefabs.get("gameItem"));
        for (var i = 0; i < this.layoutRow; i++) {
          var node = cc.instantiate(NodeCaches_1.default.prefabs.get("sectionRow"));
          var row = node.getComponent(SectionRowDelegate_1.default);
          this.scrollItems.push(row);
          node.parent = this.node;
          row.updateItem(i, 0, y, null);
          y -= this.itemHeight + this.spaceY;
          node.active = false;
        }
      };
      RecycleView.prototype.scrollToPosition = function(index) {
        var data = this.dataList[index];
      };
      RecycleView.prototype.reloadData = function() {
        this.scrollView.scrollToTop(0);
        var y = .5 * -this.itemHeight;
        for (var i = 0; i < this.scrollItems.length; i++) {
          var item = this.scrollItems[i];
          item.node.active = true;
          if (i < this.dataList.length) {
            var dataItem = this.dataList[i];
            item.updateItem(i, 0, y, dataItem);
            y -= this.itemHeight + this.spaceY;
          } else item.node.active = false;
        }
        this.setupScrollContent();
      };
      RecycleView.prototype.setupScrollContent = function() {
        var row = 0;
        for (var i = this.dataList.length - 1; i > 0; i--) {
          var rowItem = this.dataList[i];
          row++;
          if (0 == rowItem.gridType) break;
        }
        var lastHeight = row * (this.itemHeight + this.spaceY);
        var pageHeight = this.scrollView.node.height;
        var fixed = 0;
        fixed = pageHeight > lastHeight ? pageHeight - lastHeight : 0;
        this.scrollView.content.height = this.dataList.length * (this.itemHeight + this.spaceY) + fixed;
      };
      RecycleView.prototype.getPositionInView = function(item) {
        var worldPos = item.parent.convertToWorldSpaceAR(item.position);
        var viewPos = this.scrollView.node.convertToNodeSpaceAR(worldPos);
        return viewPos;
      };
      RecycleView.prototype.update = function(dt) {
        this.updateTimer += dt;
        if (this.updateTimer < this.updateInterval) return;
        this.updateTimer = 0;
        var items = this.scrollItems;
        var buffer = this.bufferZone;
        var isDown = this.node.y < this.lastContentPosY;
        var isUp = this.node.y > this.lastContentPosY;
        var curItemCount = this.scrollItems.length;
        var offset = (this.itemHeight + this.spaceY) * curItemCount;
        for (var i = 0; i < curItemCount; ++i) {
          var item = items[i];
          var itemNode = item.node;
          var viewPos = this.getPositionInView(itemNode);
          if (isDown) {
            if (viewPos.y + this.offsetStart < -buffer && itemNode.y + offset < 0) {
              var newIdx = item.index - curItemCount;
              console.log(newIdx);
              if (newIdx < this.dataList.length) {
                var newInfo = this.dataList[newIdx];
                item.updateItem(newIdx, 0, itemNode.y + offset, newInfo);
              }
            }
          } else if (isUp && viewPos.y - this.offsetStart > buffer && itemNode.y - offset > -this.node.height) {
            var newIdx = item.index + curItemCount;
            console.log(newIdx);
            if (newIdx < this.dataList.length) {
              var newInfo = this.dataList[newIdx];
              item.updateItem(newIdx, 0, itemNode.y - offset, newInfo);
            }
          }
        }
        this.lastContentPosY = this.node.y;
      };
      RecycleView.prototype.start = function() {};
      __decorate([ property(cc.ScrollView) ], RecycleView.prototype, "scrollView", void 0);
      __decorate([ property ], RecycleView.prototype, "itemHeight", void 0);
      __decorate([ property(Number) ], RecycleView.prototype, "spaceY", void 0);
      __decorate([ property ], RecycleView.prototype, "layoutRow", void 0);
      __decorate([ property ], RecycleView.prototype, "offsetStart", void 0);
      RecycleView = __decorate([ ccclass ], RecycleView);
      return RecycleView;
    }(cc.Component);
    exports.default = RecycleView;
    cc._RF.pop();
  }, {
    "../NodeCaches": "NodeCaches",
    "../games/SectionRowDelegate": "SectionRowDelegate"
  } ],
  SearchItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "d5bffZGXfpIMZ8II5BQpsmf", "SearchItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ListItemDelegate_1 = require("../widget/ListItemDelegate");
    var AppGame_1 = require("../AppGame");
    var Helper_1 = require("../utils/Helper");
    var SearchListAlert_1 = require("./SearchListAlert");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SearchItemDelegate = function(_super) {
      __extends(SearchItemDelegate, _super);
      function SearchItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.timeLabel = null;
        _this.infoLabel = null;
        _this.resultLabel = null;
        _this._item = null;
        return _this;
      }
      Object.defineProperty(SearchItemDelegate.prototype, "item", {
        get: function() {
          return this._item;
        },
        set: function(value) {
          this._item = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      SearchItemDelegate.prototype.updateItem = function(index, x, y, model) {
        _super.prototype.updateItem.call(this, index, x, y, model);
        model && (this.item = model);
      };
      SearchItemDelegate.prototype.onItemClick = function() {
        console.log(this.item);
        if (1 == parseInt(this.item.status)) {
          Helper_1.playSelectSound();
          this.node.emit("onItemSelect", this.index);
          cc.find("AppGame").getComponent(AppGame_1.default).onLogSelect(this.item);
          SearchListAlert_1.default.startFadeOut();
        }
      };
      SearchItemDelegate.prototype.updateData = function() {
        var dayjs = window["dayjs"];
        if (this.item) {
          var date = dayjs(1e3 * this.item.play_time);
          this.timeLabel.string = date.format("MM月DD日\nHH:mm");
          this.infoLabel.string = this.item.match.name + "\n" + this.item.teams[0].name + "VS" + this.item.teams[1].name;
          var status = this.item.status;
          if (1 == status) {
            this.resultLabel.string = "比赛未开始";
            this.resultLabel.node.color = cc.hexToColor("#f0a51e");
          } else if (2 == status) {
            this.resultLabel.string = "比赛进行中";
            this.resultLabel.node.color = cc.hexToColor("#45f02b");
          } else {
            this.resultLabel.string = "比赛结果\n" + this.item.teams[0].score + ":" + this.item.teams[1].score;
            this.resultLabel.node.color = cc.hexToColor("#fff4f0");
          }
        }
      };
      __decorate([ property(cc.Label) ], SearchItemDelegate.prototype, "timeLabel", void 0);
      __decorate([ property(cc.Label) ], SearchItemDelegate.prototype, "infoLabel", void 0);
      __decorate([ property(cc.Label) ], SearchItemDelegate.prototype, "resultLabel", void 0);
      SearchItemDelegate = __decorate([ ccclass ], SearchItemDelegate);
      return SearchItemDelegate;
    }(ListItemDelegate_1.default);
    exports.default = SearchItemDelegate;
    cc._RF.pop();
  }, {
    "../AppGame": "AppGame",
    "../utils/Helper": "Helper",
    "../widget/ListItemDelegate": "ListItemDelegate",
    "./SearchListAlert": "SearchListAlert"
  } ],
  SearchListAlert: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "eceffjh+HFIf4LTPDk4w+lg", "SearchListAlert");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ApiNet_1 = require("../utils/ApiNet");
    var AppGame_1 = require("../AppGame");
    var StateView_1 = require("../widget/StateView");
    var Helper_1 = require("../utils/Helper");
    var ListView_1 = require("../widget/ListView");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SearchListAlert = function() {
      function SearchListAlert() {}
      SearchListAlert.show = function(speed) {
        var _this = this;
        if (null != this._alert) {
          this._alert.setLocalZOrder(999);
          this.startFadeIn();
          return;
        }
        this._speed = speed || this._speed;
        cc.loader.loadRes("prefabs/SearchListAlert", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          _this._alert = cc.instantiate(prefab);
          _this._mask = cc.find("mask", _this._alert);
          _this._bg = cc.find("alertBg", _this._alert);
          cc.find("alertBg/searchBtn", _this._alert).on("click", function() {
            _this.onSearchClick();
          });
          var cbFadeOut = cc.callFunc(_this.onFadeOutFinish, _this);
          var cbFadeIn = cc.callFunc(_this.onFadeInFinish, _this);
          _this.actionFadeIn = cc.sequence(cc.moveBy(_this._speed, cc.p(-_this._bg.width)), cbFadeIn);
          _this.actionFadeOut = cc.sequence(cc.moveBy(_this._speed, cc.p(_this._bg.width)), cbFadeOut);
          _this._cancelButton = cc.find("alertBg/btnCancel", _this._alert);
          _this._cancelButton.on("click", _this.onButtonClicked, _this);
          _this._listView = cc.find("alertBg/scrollView/view/list", _this._alert).getComponent(ListView_1.default);
          _this._alert.parent = cc.find("Canvas");
          _this.startFadeIn();
        });
      };
      SearchListAlert.onSearchClick = function() {
        var _this = this;
        Helper_1.playBtnSound();
        var editBox = cc.find("alertBg/searchBox", this._alert).getComponent(cc.EditBox);
        var searchText = editBox.string;
        console.log(searchText);
        if ("" == searchText) return;
        var match_ids = AppGame_1.default.matchesData.map(function(item) {
          return item.id;
        }).join(",");
        var stateView = cc.find("alertBg/stateView", this._alert).getComponent(StateView_1.default);
        stateView.stateType = StateView_1.StateType.LOADING;
        ApiNet_1.default.post("/index/play/all", {
          match_id: match_ids,
          team_name: searchText
        }).then(function(res) {
          console.log(res);
          0 == res.code && _this.reloadList(res.data);
        }).catch(function(error) {});
      };
      SearchListAlert.reloadList = function(data) {
        var stateView = cc.find("alertBg/stateView", this._alert).getComponent(StateView_1.default);
        if (0 == data.length) stateView.stateType = StateView_1.StateType.EMPTY; else {
          this._listView.dataList = data;
          this._listView.reloadList();
          stateView.stateType = StateView_1.StateType.CONTENT;
        }
      };
      SearchListAlert.onButtonClicked = function(event) {
        "enterButton" == event.target.name && this._enterAction && this._enterAction();
        this.startFadeOut();
      };
      SearchListAlert.startFadeOut = function() {
        Helper_1.playAlertCloseSound();
        cc.eventManager.pauseTarget(this._alert, true);
        this._mask.runAction(cc.fadeOut(this._speed));
        this._alert.runAction(this.actionFadeOut);
      };
      SearchListAlert.onFadeInFinish = function() {
        this._alert.resumeSystemEvents(true);
      };
      SearchListAlert.onFadeOutFinish = function() {};
      SearchListAlert.onDestory = function() {
        this._alert.destroy();
        this._enterAction = null;
        this._alert = null;
        this._titleLabel = null;
        this._cancelButton = null;
        this._enterButton = null;
        this._speed = .3;
      };
      SearchListAlert.startFadeIn = function() {
        Helper_1.playAlertOpenSound();
        this._alert.pauseSystemEvents(true);
        this._alert.position = cc.p(0, 0);
        this._mask.opacity = 0;
        this._mask.runAction(cc.fadeTo(this._speed, 156));
        this._alert.runAction(this.actionFadeIn);
      };
      SearchListAlert._alert = null;
      SearchListAlert._mask = null;
      SearchListAlert._titleLabel = null;
      SearchListAlert._cancelButton = null;
      SearchListAlert._enterButton = null;
      SearchListAlert._speed = .3;
      SearchListAlert.actionFadeIn = null;
      SearchListAlert._enterAction = null;
      SearchListAlert.actionFadeOut = null;
      SearchListAlert._bg = null;
      SearchListAlert.searchItemPool = new cc.NodePool();
      SearchListAlert._listView = null;
      SearchListAlert = __decorate([ ccclass ], SearchListAlert);
      return SearchListAlert;
    }();
    exports.default = SearchListAlert;
    cc._RF.pop();
  }, {
    "../AppGame": "AppGame",
    "../utils/ApiNet": "ApiNet",
    "../utils/Helper": "Helper",
    "../widget/ListView": "ListView",
    "../widget/StateView": "StateView"
  } ],
  SectionGridItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "35399yB3nBKqZW5tpAk9Flc", "SectionGridItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var AppData_1 = require("../AppData");
    var BuyAlert_1 = require("../alert/BuyAlert");
    var BuyUtils_1 = require("../utils/BuyUtils");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SectionGridItemDelegate = function(_super) {
      __extends(SectionGridItemDelegate, _super);
      function SectionGridItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this._gridType = AppData_1.GridType.TYPE_RANG;
        _this.guestLabel = null;
        _this.homeLabel = null;
        _this.pointLabel = null;
        _this.sameLabel = null;
        _this.sizeContainer = null;
        _this.sameContainer = null;
        _this.pointContainer = null;
        _this.background = null;
        _this.line = null;
        _this.isLast = false;
        _this.config = {
          rang: {
            home: -90,
            guest: 90,
            point: -178
          },
          point: {
            home: 44,
            point: -44
          },
          daxiao: {
            home: 90,
            guest: -90,
            point: 0
          },
          duying: {
            home: -124,
            same: 24,
            guest: 192
          },
          danshuang: {
            home: -90,
            guest: 90
          }
        };
        _this._model = null;
        _this.index = 0;
        return _this;
      }
      Object.defineProperty(SectionGridItemDelegate.prototype, "model", {
        get: function() {
          return this._model;
        },
        set: function(value) {
          this._model = value;
          if (this.model) {
            this.gridType = this.model.gridType;
            this.refreshData();
          }
        },
        enumerable: true,
        configurable: true
      });
      Object.defineProperty(SectionGridItemDelegate.prototype, "gridType", {
        get: function() {
          return this._gridType;
        },
        set: function(value) {
          this._gridType = value;
          this.updateType();
        },
        enumerable: true,
        configurable: true
      });
      SectionGridItemDelegate.prototype.updateCircle = function(number) {
        this.pointLabel.string.length > number ? this.pointLabel.node.parent.width = 100 : this.pointLabel.node.parent.width = 84;
      };
      SectionGridItemDelegate.prototype.updateType = function() {
        switch (this.gridType) {
         case AppData_1.GridType.TYPE_RANG:
          this.line.active = false;
          this.guestLabel.node.active = true;
          this.sameLabel.node.active = false;
          this.pointContainer.active = false;
          this.sameContainer.active = false;
          this.sizeContainer.active = false;
          this.background.active = true;
          this.background.width = 480;
          this.node.width = 480;
          this.line.active = true;
          this.pointLabel.node.parent.active = true;
          this.homeLabel.node.x = this.config.rang.home;
          this.guestLabel.node.x = this.config.rang.guest;
          this.pointLabel.node.parent.x = this.config.rang.point;
          break;

         case AppData_1.GridType.TYPE_DANSHUANG:
          this.line.active = true;
          this.guestLabel.node.active = true;
          this.sameLabel.node.active = false;
          this.pointContainer.active = true;
          this.sameContainer.active = false;
          this.background.active = true;
          this.background.width = 480;
          this.node.width = 480;
          this.sizeContainer.active = false;
          this.line.active = true;
          this.pointLabel.node.parent.active = false;
          this.homeLabel.node.x = this.config.danshuang.home;
          this.guestLabel.node.x = this.config.danshuang.guest;
          break;

         case AppData_1.GridType.TYPE_DAXIAO:
          this.sameLabel.node.active = false;
          this.pointContainer.active = false;
          this.sameContainer.active = false;
          this.background.active = true;
          this.background.width = 480;
          this.line.active = false;
          this.node.width = 480;
          this.pointLabel.node.parent.active = true;
          this.homeLabel.node.x = this.config.daxiao.home;
          this.guestLabel.node.x = this.config.daxiao.guest;
          this.guestLabel.node.active = true;
          this.pointLabel.node.parent.x = this.config.daxiao.point;
          this.sizeContainer.active = true;
          this.guestLabel.node.active = true;
          break;

         case AppData_1.GridType.TYPE_DUYING:
          this.line.active = false;
          this.guestLabel.node.active = true;
          this.sameLabel.node.active = true;
          this.pointContainer.active = false;
          this.sameContainer.active = true;
          this.background.active = true;
          this.background.width = 480;
          this.node.width = 480;
          this.sizeContainer.active = false;
          this.pointLabel.node.parent.active = false;
          this.homeLabel.node.x = this.config.duying.home;
          this.guestLabel.node.x = this.config.duying.guest;
          this.sameLabel.node.x = this.config.duying.same;
          break;

         case AppData_1.GridType.TYPE_BODAN:
         case AppData_1.GridType.TYPE_ZONG:
         case AppData_1.GridType.TYPE_ZHUDUI:
         case AppData_1.GridType.TYPE_KEDUI:
          this.sameLabel.node.active = false;
          this.guestLabel.node.active = false;
          this.pointContainer.active = false;
          this.sameContainer.active = false;
          this.line.active = false;
          this.background.active = true;
          this.background.width = 223;
          this.node.width = 223;
          this.pointLabel.node.parent.active = true;
          this.homeLabel.node.x = this.config.point.home;
          this.pointLabel.node.parent.x = this.config.point.point;
          this.sizeContainer.active = false;
        }
      };
      SectionGridItemDelegate.prototype.onItemClick = function() {
        console.log("----");
        BuyAlert_1.default.show("交易单", this.gridType, this.model.item, function() {
          return BuyUtils_1.buy();
        }, .3);
      };
      SectionGridItemDelegate.prototype.refreshData = function() {
        if (this._model) switch (this.gridType) {
         case AppData_1.GridType.SECTION_TITLE:
          break;

         case AppData_1.GridType.TYPE_RANG:
          this.homeLabel.string = parseFloat(this._model.item.home).toFixed(2);
          this.guestLabel.string = parseFloat(this._model.item.guest).toFixed(2);
          this.pointLabel.string = "" + this._model.item.handicap;
          this.pointLabel.fontSize = 24;
          this.pointLabel.lineHeight = 24;
          this.pointLabel.node.parent.width = 84;
          break;

         case AppData_1.GridType.TYPE_DANSHUANG:
          this.homeLabel.string = parseFloat(this._model.item.sd_1).toFixed(2);
          this.guestLabel.string = parseFloat(this._model.item.sd_2).toFixed(2);
          break;

         case AppData_1.GridType.TYPE_DUYING:
          this.homeLabel.string = parseFloat(this._model.item.home).toFixed(2);
          this.guestLabel.string = parseFloat(this._model.item.guest).toFixed(2);
          this.sameLabel.string = parseFloat(this._model.item.same).toFixed(2);
          break;

         case AppData_1.GridType.TYPE_DAXIAO:
          this.homeLabel.string = parseFloat(this._model.item.home).toFixed(2);
          this.guestLabel.string = parseFloat(this._model.item.guest).toFixed(2);
          this.pointLabel.string = this._model.item.under;
          this.pointLabel.fontSize = 30;
          this.pointLabel.lineHeight = 30;
          this.pointLabel.node.parent.width = 84;
          break;

         case AppData_1.GridType.TYPE_BODAN:
          this.homeLabel.string = parseFloat(this._model.item.value).toFixed(2);
          this.pointLabel.fontSize = 30;
          this.pointLabel.lineHeight = 30;
          this.pointLabel.node.parent.width = 84;
          this.pointLabel.string = this._model.item.label;
          break;

         case AppData_1.GridType.TYPE_ZHUDUI:
          this.homeLabel.string = parseFloat(this.model.item.value).toFixed(2);
          this.pointLabel.fontSize = 30;
          this.pointLabel.lineHeight = 30;
          this.pointLabel.string = this.model.isLast ? this.model.item.label + "球+" : this.model.item.label + "球";
          this.pointLabel.node.parent.width = this.model.isLast ? 92 : 84;
          break;

         case AppData_1.GridType.TYPE_KEDUI:
          this.pointLabel.fontSize = 30;
          this.pointLabel.lineHeight = 30;
          this.homeLabel.string = parseFloat(this.model.item.value).toFixed(2);
          this.pointLabel.string = this.model.isLast ? this.model.item.label + "球+" : this.model.item.label + "球";
          this.pointLabel.node.parent.width = this.model.isLast ? 92 : 84;
          break;

         case AppData_1.GridType.TYPE_ZONG:
          this.homeLabel.string = parseFloat(this.model.item.value).toFixed(2);
          this.pointLabel.fontSize = 30;
          this.pointLabel.lineHeight = 30;
          this.pointLabel.node.parent.width = 84;
          this.pointLabel.string = this.model.isLast ? this.model.item.label + "+" : this.model.item.label;
        }
      };
      SectionGridItemDelegate.prototype.updateLayout = function(x, y) {
        this.node.x = x;
        this.node.y = y;
      };
      __decorate([ property(cc.Label) ], SectionGridItemDelegate.prototype, "guestLabel", void 0);
      __decorate([ property(cc.Label) ], SectionGridItemDelegate.prototype, "homeLabel", void 0);
      __decorate([ property(cc.Label) ], SectionGridItemDelegate.prototype, "pointLabel", void 0);
      __decorate([ property(cc.Label) ], SectionGridItemDelegate.prototype, "sameLabel", void 0);
      __decorate([ property(cc.Node) ], SectionGridItemDelegate.prototype, "sizeContainer", void 0);
      __decorate([ property(cc.Node) ], SectionGridItemDelegate.prototype, "sameContainer", void 0);
      __decorate([ property(cc.Node) ], SectionGridItemDelegate.prototype, "pointContainer", void 0);
      __decorate([ property(cc.Node) ], SectionGridItemDelegate.prototype, "background", void 0);
      __decorate([ property(cc.Node) ], SectionGridItemDelegate.prototype, "line", void 0);
      __decorate([ property({
        type: cc.Enum(AppData_1.GridType)
      }) ], SectionGridItemDelegate.prototype, "gridType", null);
      SectionGridItemDelegate = __decorate([ ccclass ], SectionGridItemDelegate);
      return SectionGridItemDelegate;
    }(cc.Component);
    exports.default = SectionGridItemDelegate;
    cc._RF.pop();
  }, {
    "../AppData": "AppData",
    "../alert/BuyAlert": "BuyAlert",
    "../utils/BuyUtils": "BuyUtils"
  } ],
  SectionGridItem: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "cc9d6pFqING0JYhqCwf5oRs", "SectionGridItem");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SectionGridItem = function() {
      function SectionGridItem() {
        this.item = null;
        this.row = 0;
        this.col = 0;
        this.width = 0;
        this.height = 0;
        this.children = [];
        this.isLast = false;
      }
      SectionGridItem = __decorate([ ccclass ], SectionGridItem);
      return SectionGridItem;
    }();
    exports.default = SectionGridItem;
    cc._RF.pop();
  }, {} ],
  SectionItemDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "4add1qqP/RBCri0dEh70REq", "SectionItemDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ChooseItemModel_1 = require("./model/ChooseItemModel");
    var BuyAlert_1 = require("../alert/BuyAlert");
    var ChooseItemType1Delegate_1 = require("./ChooseItemType1Delegate");
    var ChooseItemType2Delegate_1 = require("./ChooseItemType2Delegate");
    var ChooseItemType5Delegate_1 = require("./ChooseItemType5Delegate");
    var ChooseItemType4Delegate_1 = require("./ChooseItemType4Delegate");
    var ChooseItemType3Delegate_1 = require("./ChooseItemType3Delegate");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SectionItemDelegate = function(_super) {
      __extends(SectionItemDelegate, _super);
      function SectionItemDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.container = null;
        _this.sectionHeader = null;
        _this.sectionTitle = null;
        _this.sectionGridPrefab = null;
        _this.icon = null;
        _this.items = [];
        _this.listItems = [];
        _this.index = 0;
        _this.spaceX = 0;
        _this.spaceY = 0;
        _this.preItemCount = 10;
        _this._type = 0;
        _this.col = 0;
        _this.row = 0;
        _this.lastRow = 0;
        _this.itemWidth = 0;
        _this.itemHeight = 0;
        return _this;
      }
      Object.defineProperty(SectionItemDelegate.prototype, "type", {
        get: function() {
          return this._type;
        },
        set: function(v) {
          this._type = v;
          this.updatePreItem();
        },
        enumerable: true,
        configurable: true
      });
      SectionItemDelegate.prototype.onLoad = function() {};
      SectionItemDelegate.prototype.updatePreItem = function() {
        switch (this.type) {
         case 0:
          this.preItemCount = 20;
          break;

         case 1:
          this.preItemCount = 40;
          break;

         case 2:
          this.preItemCount = 10;
          break;

         case 3:
         case 4:
          this.preItemCount = 5;
        }
      };
      SectionItemDelegate.prototype.preItems = function() {
        this.itemWidth = this.sectionGridPrefab.data.width;
        this.itemHeight = this.sectionGridPrefab.data.height;
        this.col = Math.floor((this.container.width + this.spaceX) / (this.itemWidth + this.spaceX));
        this.row = Math.ceil(this.preItemCount / this.col);
        for (var i = 0; i < this.preItemCount; i++) {
          var itemPrefab = cc.instantiate(this.sectionGridPrefab);
          this.container.addChild(itemPrefab);
          var sectionGridItemDelegate = itemPrefab.getComponent(SectionGridItemDelegate_1.default);
          this.listItems.push(sectionGridItemDelegate);
          sectionGridItemDelegate.node.active = false;
        }
        this.node.active = false;
        this.lastRow = this.row;
      };
      SectionItemDelegate.prototype.reloadData = function() {
        this.node.active = true;
        this.row = Math.ceil(this.items.length / this.col);
        if (this.listItems.length > this.items.length) for (var i = 0; i < this.listItems.length; i++) {
          var gridItemDelegate = this.listItems[i];
          if (i < this.items.length) {
            var item = this.items[i];
            gridItemDelegate.node.active = true;
            var itemRow = Math.floor(i / this.col);
            var itemCol = Math.floor(i % this.col);
            var vec = this.getLayoutVec(itemRow, itemCol);
            gridItemDelegate.updateItem(vec.x, vec.y, item, true);
          } else gridItemDelegate.node.active = false;
        } else for (var i = 0; i < this.items.length; i++) {
          var item = this.items[i];
          var itemRow = Math.floor(i / this.col);
          var itemCol = Math.floor(i % this.col);
          if (i < this.listItems.length) {
            var gridItemDelegate = this.listItems[i];
            gridItemDelegate.node.active = true;
            var vec = this.getLayoutVec(itemRow, itemCol);
            gridItemDelegate.updateItem(vec.x, vec.y, item, true);
          } else {
            var itemPrefab = cc.instantiate(this.sectionGridPrefab);
            this.container.addChild(itemPrefab);
            var sectionGridItemDelegate = itemPrefab.getComponent(SectionGridItemDelegate_1.default);
            this.listItems.push(sectionGridItemDelegate);
            var itemRow_1 = Math.floor(i / this.col);
            var itemCol_1 = Math.floor(i % this.col);
            var vec = this.getLayoutVec(itemRow_1, itemCol_1);
            sectionGridItemDelegate.updateItem(vec.x, vec.y, item, true);
          }
        }
        console.log("row -----" + this.row);
        this.container.height = this.row * this.itemHeight + (this.row - 1) * this.spaceY;
        this.node.height = this.row * this.itemHeight + (this.row - 1) * this.spaceY + 80 + 60;
        console.log(this.node.height);
      };
      SectionItemDelegate.prototype.getLayoutVec = function(row, col) {
        var x = .5 * this.itemWidth + col * (this.itemWidth + this.spaceX);
        var y = -row * (this.itemHeight + this.spaceY) - .5 * this.itemHeight;
        console.log(x, y);
        return {
          x: x,
          y: y
        };
      };
      SectionItemDelegate.prototype.onSectionItemClick = function(i, model) {
        if (this.type > 0) {
          console.log("-----");
          BuyAlert_1.default.show("交易单", this.type, model, function() {}, .3);
        }
      };
      SectionItemDelegate.prototype.setupData = function(node, model) {
        var typeDelegate = null;
        switch (this.type) {
         case 1:
          typeDelegate = node.getComponent(ChooseItemType1Delegate_1.default);
          break;

         case 2:
          typeDelegate = node.getComponent(ChooseItemType2Delegate_1.default);
          break;

         case 3:
          typeDelegate = node.getComponent(ChooseItemType3Delegate_1.default);
          break;

         case 4:
          typeDelegate = node.getComponent(ChooseItemType4Delegate_1.default);
          break;

         case 5:
          typeDelegate = node.getComponent(ChooseItemType5Delegate_1.default);
        }
        typeDelegate && (typeDelegate.item = model);
      };
      __decorate([ property(cc.Node) ], SectionItemDelegate.prototype, "container", void 0);
      __decorate([ property(cc.Sprite) ], SectionItemDelegate.prototype, "sectionHeader", void 0);
      __decorate([ property(cc.Label) ], SectionItemDelegate.prototype, "sectionTitle", void 0);
      __decorate([ property(cc.Prefab) ], SectionItemDelegate.prototype, "sectionGridPrefab", void 0);
      __decorate([ property(cc.Sprite) ], SectionItemDelegate.prototype, "icon", void 0);
      __decorate([ property(ChooseItemModel_1.default) ], SectionItemDelegate.prototype, "items", void 0);
      __decorate([ property(Number) ], SectionItemDelegate.prototype, "spaceX", void 0);
      __decorate([ property(Number) ], SectionItemDelegate.prototype, "spaceY", void 0);
      SectionItemDelegate = __decorate([ ccclass ], SectionItemDelegate);
      return SectionItemDelegate;
    }(cc.Component);
    exports.default = SectionItemDelegate;
    cc._RF.pop();
  }, {
    "../alert/BuyAlert": "BuyAlert",
    "./ChooseItemType1Delegate": "ChooseItemType1Delegate",
    "./ChooseItemType2Delegate": "ChooseItemType2Delegate",
    "./ChooseItemType3Delegate": "ChooseItemType3Delegate",
    "./ChooseItemType4Delegate": "ChooseItemType4Delegate",
    "./ChooseItemType5Delegate": "ChooseItemType5Delegate",
    "./SectionGridItemDelegate": "SectionGridItemDelegate",
    "./model/ChooseItemModel": "ChooseItemModel"
  } ],
  SectionRowDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "bb29dUDOgpL+pT7Pes+GjZT", "SectionRowDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var AppData_1 = require("../AppData");
    var SectionGridItemDelegate_1 = require("./SectionGridItemDelegate");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SectionRowDelegate = function(_super) {
      __extends(SectionRowDelegate, _super);
      function SectionRowDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.container = null;
        _this.sectionHeader = null;
        _this.sectionTitle = null;
        _this.gridItem = null;
        _this.icon = null;
        _this.spaceX = 30;
        _this.sectionGridItems = [];
        _this._type = 0;
        _this._children = [];
        _this.index = 0;
        _this._model = null;
        return _this;
      }
      Object.defineProperty(SectionRowDelegate.prototype, "model", {
        get: function() {
          return this._model;
        },
        set: function(value) {
          this._model = value;
          if (this.model) {
            this.type = this.model.gridType;
            this.type == AppData_1.GridType.SECTION_TITLE ? this.sectionTitle.string = this.model.section.name : this.children = this.model.children;
          }
        },
        enumerable: true,
        configurable: true
      });
      Object.defineProperty(SectionRowDelegate.prototype, "children", {
        get: function() {
          return this._children;
        },
        set: function(value) {
          this._children = value;
          this.updateData();
        },
        enumerable: true,
        configurable: true
      });
      SectionRowDelegate.prototype.updateData = function() {
        for (var i = 0; i < this.sectionGridItems.length; i++) {
          var gridItemDelegate = this.sectionGridItems[i];
          if (i < this.children.length) {
            gridItemDelegate.node.active = true;
            var model = this.children[i];
            model.isLast = i == this.children.length - 1;
            gridItemDelegate.model = model;
          } else gridItemDelegate.node.active = false;
        }
      };
      Object.defineProperty(SectionRowDelegate.prototype, "type", {
        get: function() {
          return this._type;
        },
        set: function(value) {
          this._type = value;
          this.updateLayout();
        },
        enumerable: true,
        configurable: true
      });
      SectionRowDelegate.prototype.updateLayout = function() {
        switch (this.type) {
         case AppData_1.GridType.SECTION_TITLE:
          this.sectionHeader.active = true;
          this.container.active = false;
          break;

         case AppData_1.GridType.TYPE_DAXIAO:
         case AppData_1.GridType.TYPE_DUYING:
         case AppData_1.GridType.TYPE_RANG:
         case AppData_1.GridType.TYPE_DANSHUANG:
          this.sectionHeader.active = false;
          this.container.active = true;
          this.updateType(3);
          break;

         case AppData_1.GridType.TYPE_BODAN:
         case AppData_1.GridType.TYPE_ZONG:
         case AppData_1.GridType.TYPE_KEDUI:
         case AppData_1.GridType.TYPE_ZHUDUI:
          this.sectionHeader.active = false;
          this.container.active = true;
          this.updateType(6);
        }
      };
      SectionRowDelegate.prototype.updateType = function(number) {
        for (var i = 0; i < this.sectionGridItems.length; i++) {
          var gridItemDelegate = this.sectionGridItems[i];
          gridItemDelegate.gridType = this.type;
          gridItemDelegate.node.active = i < number;
          var x = .5 * gridItemDelegate.node.width + i * (gridItemDelegate.node.width + this.spaceX);
          gridItemDelegate.updateLayout(x, 0);
        }
      };
      SectionRowDelegate.prototype.updateItem = function(newIdx, x, y, newInfo) {
        this.index = newIdx;
        this.node.x = x;
        this.node.y = y;
        this.model = newInfo;
      };
      __decorate([ property(cc.Node) ], SectionRowDelegate.prototype, "container", void 0);
      __decorate([ property(cc.Node) ], SectionRowDelegate.prototype, "sectionHeader", void 0);
      __decorate([ property(cc.Label) ], SectionRowDelegate.prototype, "sectionTitle", void 0);
      __decorate([ property(cc.Prefab) ], SectionRowDelegate.prototype, "gridItem", void 0);
      __decorate([ property(cc.Sprite) ], SectionRowDelegate.prototype, "icon", void 0);
      __decorate([ property ], SectionRowDelegate.prototype, "spaceX", void 0);
      __decorate([ property(SectionGridItemDelegate_1.default) ], SectionRowDelegate.prototype, "sectionGridItems", void 0);
      __decorate([ property({
        type: cc.Enum(AppData_1.GridType)
      }) ], SectionRowDelegate.prototype, "type", null);
      SectionRowDelegate = __decorate([ ccclass ], SectionRowDelegate);
      return SectionRowDelegate;
    }(cc.Component);
    exports.default = SectionRowDelegate;
    cc._RF.pop();
  }, {
    "../AppData": "AppData",
    "./SectionGridItemDelegate": "SectionGridItemDelegate"
  } ],
  SectionScrollDelegate: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "995adXde/RHK5ljGTYxNrMZ", "SectionScrollDelegate");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var AppData_1 = require("../AppData");
    var NodeCaches_1 = require("../NodeCaches");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var SectionScrollDelegate = function(_super) {
      __extends(SectionScrollDelegate, _super);
      function SectionScrollDelegate() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.content = null;
        _this.isClick = false;
        _this.typeUpArrow = null;
        _this.typeDownArrow = null;
        return _this;
      }
      SectionScrollDelegate.prototype.updateContentLayout = function() {};
      SectionScrollDelegate.prototype.onScroll = function(scrollView, eventType) {
        eventType != cc.ScrollView.EventType.SCROLL_ENDED && eventType != cc.ScrollView.EventType.SCROLLING || this.updateMenuIndex(scrollView.getScrollOffset(), eventType, scrollView);
        var number = Math.floor(scrollView.getScrollOffset().y);
        console.log(number);
        this.typeUpArrow.active = number > 0;
        this.typeDownArrow.active = number < Math.floor(Math.abs(scrollView.getMaxScrollOffset().y));
      };
      SectionScrollDelegate.prototype.updateMenuIndex = function(scrollOffset, eventType, scrollView) {
        if (this.isClick) {
          console.log("---");
          eventType == cc.ScrollView.EventType.SCROLL_ENDED && (this.isClick = false);
        } else {
          var offsetY = Math.floor(scrollOffset.y);
          if (offsetY < 0) {
            var component = AppData_1.default.showMenus[0];
            component.updateOther();
            component.item.selected = true;
            component.refresh();
            NodeCaches_1.default.lastSelectTypeMenu = component;
          } else if (offsetY >= scrollView.getMaxScrollOffset().y) {
            var component = AppData_1.default.showMenus[AppData_1.default.showMenus.length - 1];
            component.updateOther();
            component.item.selected = true;
            component.refresh();
            NodeCaches_1.default.lastSelectTypeMenu = component;
          } else for (var i = 0; i < AppData_1.default.showMenus.length; i++) {
            var component = AppData_1.default.showMenus[i];
            if (offsetY >= -component.scrollStartY && offsetY < -component.scrollEndY) {
              component.updateOther();
              component.item.selected = true;
              component.refresh();
              NodeCaches_1.default.lastSelectTypeMenu = component;
              break;
            }
          }
        }
      };
      __decorate([ property(cc.Node) ], SectionScrollDelegate.prototype, "content", void 0);
      __decorate([ property(cc.Node) ], SectionScrollDelegate.prototype, "typeUpArrow", void 0);
      __decorate([ property(cc.Node) ], SectionScrollDelegate.prototype, "typeDownArrow", void 0);
      SectionScrollDelegate = __decorate([ ccclass ], SectionScrollDelegate);
      return SectionScrollDelegate;
    }(cc.Component);
    exports.default = SectionScrollDelegate;
    cc._RF.pop();
  }, {
    "../AppData": "AppData",
    "../NodeCaches": "NodeCaches"
  } ],
  StateView: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "ab3dea7YBZA5ZnS02VLdp35", "StateView");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var StateType;
    (function(StateType) {
      StateType[StateType["EMPTY"] = 0] = "EMPTY";
      StateType[StateType["LOADING"] = 1] = "LOADING";
      StateType[StateType["CONTENT"] = 2] = "CONTENT";
    })(StateType = exports.StateType || (exports.StateType = {}));
    var StateView = function(_super) {
      __extends(StateView, _super);
      function StateView() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.loading = null;
        _this.emptyView = null;
        _this._stateType = StateType.LOADING;
        return _this;
      }
      Object.defineProperty(StateView.prototype, "stateType", {
        get: function() {
          return this._stateType;
        },
        set: function(value) {
          this._stateType = value;
          this.updateContent();
        },
        enumerable: true,
        configurable: true
      });
      StateView.prototype.start = function() {};
      StateView.prototype.updateContent = function() {
        this.loading.active = false;
        this.emptyView.node.active = false;
        switch (this.stateType) {
         case StateType.LOADING:
          this.loading.active = true;
          this.emptyView.node.active = false;
          break;

         case StateType.EMPTY:
          this.loading.active = false;
          this.emptyView.node.active = true;
          break;

         case StateType.CONTENT:
        }
      };
      __decorate([ property(cc.Node) ], StateView.prototype, "loading", void 0);
      __decorate([ property(cc.Sprite) ], StateView.prototype, "emptyView", void 0);
      __decorate([ property ], StateView.prototype, "stateType", null);
      StateView = __decorate([ ccclass ], StateView);
      return StateView;
    }(cc.Component);
    exports.default = StateView;
    cc._RF.pop();
  }, {} ],
  TabLayout: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "9c1e53ebQxIganUdRGWTVtY", "TabLayout");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var Tab_1 = require("./Tab");
    var Helper_1 = require("../utils/Helper");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var TabLayout = function(_super) {
      __extends(TabLayout, _super);
      function TabLayout() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.selectedTab = null;
        return _this;
      }
      TabLayout.prototype.onLoad = function() {
        this.setupTabs();
      };
      TabLayout.prototype.clearTabs = function() {
        this.node.removeAllChildren();
      };
      TabLayout.prototype.setupTabs = function() {
        var _this = this;
        var _loop_1 = function(i) {
          var child = this_1.node.children[i];
          var component = child.getComponent(Tab_1.default);
          console.log(component);
          if (component) {
            child.on("click", function() {
              _this.onTabSelected(component, i);
            });
            component.selected = false;
            if (0 == i) {
              component.selected = true;
              this_1.selectedTab = component;
            }
          }
        };
        var this_1 = this;
        for (var i = 0; i < this.node.childrenCount; i++) _loop_1(i);
      };
      TabLayout.prototype.onTabSelected = function(component, index) {
        console.log(index);
        Helper_1.playSelectSound();
        if (component != this.selectedTab) {
          this.selectedTab.selected = false;
          component.selected = true;
          this.selectedTab = component;
          this.node.emit("onTabSelected", index);
        }
      };
      TabLayout = __decorate([ ccclass ], TabLayout);
      return TabLayout;
    }(cc.Component);
    exports.default = TabLayout;
    cc._RF.pop();
  }, {
    "../utils/Helper": "Helper",
    "./Tab": "Tab"
  } ],
  TableViewCell: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "a9c18jbEdBCiLaLKAzQBap1", "TableViewCell");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var TableViewCell = function(_super) {
      __extends(TableViewCell, _super);
      function TableViewCell() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.tableView = null;
        _this._isCellInit_ = false;
        _this._longClicked_ = false;
        return _this;
      }
      TableViewCell.prototype._cellAddMethodToNode_ = function() {
        this.node["clicked"] = this.clicked.bind(this);
      };
      TableViewCell.prototype._cellAddTouch_ = function() {
        var _this = this;
        this.node.on(cc.Node.EventType.TOUCH_START, function(event) {
          if (true === _this.node.active && 0 !== _this.node.opacity && !_this._longClicked_) {
            _this._longClicked_ = true;
            _this.scheduleOnce(_this._longClicked, 1.5);
          }
        }, this);
        this.node.on(cc.Node.EventType.TOUCH_MOVE, function() {
          if (_this._longClicked_) {
            _this._longClicked_ = false;
            _this.unschedule(_this._longClicked);
          }
        }, this);
        this.node.on(cc.Node.EventType.TOUCH_END, function() {
          _this.clicked();
          if (_this._longClicked_) {
            _this._longClicked_ = false;
            _this.unschedule(_this._longClicked);
          }
        }, this);
        this.node.on(cc.Node.EventType.TOUCH_CANCEL, function() {
          if (_this._longClicked_) {
            _this._longClicked_ = false;
            _this.unschedule(_this._longClicked);
          }
        }, this);
      };
      TableViewCell.prototype._cellInit_ = function(tableView) {
        this.tableView = tableView;
        if (!this._isCellInit_) {
          this._cellAddMethodToNode_();
          this._cellAddTouch_();
          this._isCellInit_ = true;
        }
      };
      TableViewCell.prototype._longClicked = function() {
        this._longClicked_ = false;
        this.node.emit(cc.Node.EventType.TOUCH_CANCEL);
        this.longClicked();
      };
      TableViewCell.prototype.longClicked = function() {};
      TableViewCell.prototype.clicked = function() {};
      TableViewCell.prototype.init = function(index, data, reload, group) {};
      __decorate([ property ], TableViewCell.prototype, "tableView", void 0);
      TableViewCell = __decorate([ ccclass ], TableViewCell);
      return TableViewCell;
    }(cc.Component);
    exports.default = TableViewCell;
    cc._RF.pop();
  }, {} ],
  Tab: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "416b2Q9ZMtJF4UoazHPpQX2", "Tab");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var Tab = function(_super) {
      __extends(Tab, _super);
      function Tab() {
        var _this = null !== _super && _super.apply(this, arguments) || this;
        _this.tabSelected = null;
        _this.tabNormal = null;
        _this.label = null;
        _this._selected = false;
        _this.tag = "";
        return _this;
      }
      Object.defineProperty(Tab.prototype, "selected", {
        get: function() {
          return this._selected;
        },
        set: function(value) {
          this._selected = value;
          this.updateTab();
        },
        enumerable: true,
        configurable: true
      });
      Tab.prototype.updateTab = function() {
        this.tabSelected.node.active = this.selected;
        this.tabNormal.node.active = !this.selected;
      };
      Tab.prototype.start = function() {};
      __decorate([ property(cc.Sprite) ], Tab.prototype, "tabSelected", void 0);
      __decorate([ property(cc.Sprite) ], Tab.prototype, "tabNormal", void 0);
      __decorate([ property(cc.Label) ], Tab.prototype, "label", void 0);
      __decorate([ property ], Tab.prototype, "tag", void 0);
      __decorate([ property ], Tab.prototype, "selected", null);
      Tab = __decorate([ ccclass ], Tab);
      return Tab;
    }(cc.Component);
    exports.default = Tab;
    cc._RF.pop();
  }, {} ],
  TeamModel: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "726c7HwoRhGoKTG1pycrIHL", "TeamModel");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var TeamModel = function() {
      function TeamModel() {
        this.half_score = 0;
        this.has_follow = 0;
        this.has_home = 0;
        this.id = 0;
        this.logo = "";
        this.name = "";
        this.red = 0;
        this.score = 0;
        this.yellow = 0;
      }
      return TeamModel;
    }();
    exports.default = TeamModel;
    cc._RF.pop();
  }, {} ],
  TipsAlert: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "18704gQ2StD/Ktvo/FoPwI0", "TipsAlert");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var Helper_1 = require("../utils/Helper");
    var _a = cc._decorator, ccclass = _a.ccclass, property = _a.property;
    var TipsAlert = function() {
      function TipsAlert() {}
      TipsAlert_1 = TipsAlert;
      TipsAlert.show = function(speed, title, content, enterAction, submitString, cancelString, showCancel) {
        var _this = this;
        void 0 === submitString && (submitString = "确定");
        void 0 === cancelString && (cancelString = "取消");
        void 0 === showCancel && (showCancel = false);
        if (null != this._alert) return;
        this._speed = speed || this._speed;
        this._enterAction = enterAction;
        cc.loader.loadRes("prefabs/TipsAlert", cc.Prefab, function(error, prefab) {
          if (error) {
            cc.error(error);
            return;
          }
          _this._alert = cc.instantiate(prefab);
          _this._mask = cc.find("mask", _this._alert);
          var cbFadeOut = cc.callFunc(_this.onFadeOutFinish, _this);
          var cbFadeIn = cc.callFunc(_this.onFadeInFinish, _this);
          cc.find("alert/title", _this._alert).getComponent(cc.Label).string = title;
          cc.find("alert/content", _this._alert).getComponent(cc.Label).string = content;
          _this._enterButton = cc.find("alert/btnSubmit", _this._alert);
          _this._cancelButton = cc.find("alert/btnCancel", _this._alert);
          cc.find("label", _this._enterButton).getComponent(cc.Label).string = submitString;
          cc.find("label", _this._cancelButton).getComponent(cc.Label).string = cancelString;
          if (showCancel) {
            _this._enterButton.x = 160;
            _this._cancelButton.x = -160;
            _this._cancelButton.active = true;
          } else {
            _this._enterButton.x = 0;
            _this._cancelButton.x = 0;
            _this._cancelButton.active = false;
          }
          _this._enterButton.on("click", TipsAlert_1.onButtonClicked, _this);
          _this._cancelButton.on("click", TipsAlert_1.onButtonClicked, _this);
          _this.actionFadeIn = cc.sequence(cc.spawn(cc.fadeIn(_this._speed), cc.scaleTo(_this._speed, 1.1).easing(cc.easeBackOut())), cbFadeIn);
          _this.actionFadeOut = cc.sequence(cc.spawn(cc.fadeOut(_this._speed), cc.scaleTo(_this._speed, .5).easing(cc.easeBackIn())), cbFadeOut);
          _this._alert.parent = cc.find("Canvas");
          _this.startFadeIn();
        });
      };
      TipsAlert.onButtonClicked = function(event) {
        if ("btnSubmit" == event.target.name) {
          this._enterAction && this._enterAction();
          TipsAlert_1.startFadeOut();
        }
        this.startFadeOut();
      };
      TipsAlert.startFadeOut = function() {
        Helper_1.playAlertCloseSound();
        cc.eventManager.pauseTarget(this._alert, true);
        this._mask.runAction(cc.fadeOut(this._speed));
        this._alert.runAction(this.actionFadeOut);
      };
      TipsAlert.onFadeInFinish = function() {
        cc.eventManager.resumeTarget(this._alert, true);
      };
      TipsAlert.onFadeOutFinish = function() {
        this.onDestory();
      };
      TipsAlert.onDestory = function() {
        this._alert.destroy();
        this._enterAction = null;
        this._alert = null;
        this._titleLabel = null;
        this._enterButton = null;
        this._speed = .3;
      };
      TipsAlert.startFadeIn = function() {
        Helper_1.playAlertOpenSound();
        cc.eventManager.pauseTarget(this._alert, true);
        this._alert.position = cc.p(0, 0);
        this._mask.opacity = 0;
        this._mask.runAction(cc.fadeTo(this._speed, 156));
        this._alert.runAction(this.actionFadeIn);
      };
      TipsAlert._alert = null;
      TipsAlert._mask = null;
      TipsAlert._titleLabel = null;
      TipsAlert._enterButton = null;
      TipsAlert._speed = .3;
      TipsAlert.actionFadeIn = null;
      TipsAlert._enterAction = null;
      TipsAlert.actionFadeOut = null;
      TipsAlert._bg = null;
      TipsAlert._cancelButton = null;
      TipsAlert = TipsAlert_1 = __decorate([ ccclass ], TipsAlert);
      return TipsAlert;
      var TipsAlert_1;
    }();
    exports.default = TipsAlert;
    cc._RF.pop();
  }, {
    "../utils/Helper": "Helper"
  } ],
  base64: [ function(require, module, exports) {
    (function(global) {
      "use strict";
      cc._RF.push(module, "ef11fj/jZJEuo8EMUvM+iJu", "base64");
      "use strict";
      var _typeof = "function" === typeof Symbol && "symbol" === typeof Symbol.iterator ? function(obj) {
        return typeof obj;
      } : function(obj) {
        return obj && "function" === typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
      };
      (function(global, factory) {
        "object" === ("undefined" === typeof exports ? "undefined" : _typeof(exports)) && "undefined" !== typeof module ? module.exports = factory(global) : "function" === typeof define && define.amd ? define(factory) : factory(global);
      })("undefined" !== typeof self ? self : "undefined" !== typeof window ? window : "undefined" !== typeof global ? global : void 0, function(global) {
        var _Base64 = global.Base64;
        var version = "2.4.3";
        var buffer;
        if ("undefined" !== typeof module && module.exports) try {
          buffer = require("buffer").Buffer;
        } catch (err) {}
        var b64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        var b64tab = function(bin) {
          var t = {};
          for (var i = 0, l = bin.length; i < l; i++) t[bin.charAt(i)] = i;
          return t;
        }(b64chars);
        var fromCharCode = String.fromCharCode;
        var cb_utob = function cb_utob(c) {
          if (c.length < 2) {
            var cc = c.charCodeAt(0);
            return cc < 128 ? c : cc < 2048 ? fromCharCode(192 | cc >>> 6) + fromCharCode(128 | 63 & cc) : fromCharCode(224 | cc >>> 12 & 15) + fromCharCode(128 | cc >>> 6 & 63) + fromCharCode(128 | 63 & cc);
          }
          var cc = 65536 + 1024 * (c.charCodeAt(0) - 55296) + (c.charCodeAt(1) - 56320);
          return fromCharCode(240 | cc >>> 18 & 7) + fromCharCode(128 | cc >>> 12 & 63) + fromCharCode(128 | cc >>> 6 & 63) + fromCharCode(128 | 63 & cc);
        };
        var re_utob = /[\uD800-\uDBFF][\uDC00-\uDFFFF]|[^\x00-\x7F]/g;
        var utob = function utob(u) {
          return u.replace(re_utob, cb_utob);
        };
        var cb_encode = function cb_encode(ccc) {
          var padlen = [ 0, 2, 1 ][ccc.length % 3], ord = ccc.charCodeAt(0) << 16 | (ccc.length > 1 ? ccc.charCodeAt(1) : 0) << 8 | (ccc.length > 2 ? ccc.charCodeAt(2) : 0), chars = [ b64chars.charAt(ord >>> 18), b64chars.charAt(ord >>> 12 & 63), padlen >= 2 ? "=" : b64chars.charAt(ord >>> 6 & 63), padlen >= 1 ? "=" : b64chars.charAt(63 & ord) ];
          return chars.join("");
        };
        var btoa = global.btoa ? function(b) {
          return global.btoa(b);
        } : function(b) {
          return b.replace(/[\s\S]{1,3}/g, cb_encode);
        };
        var _encode = buffer ? buffer.from && buffer.from !== Uint8Array.from ? function(u) {
          return (u.constructor === buffer.constructor ? u : buffer.from(u)).toString("base64");
        } : function(u) {
          return (u.constructor === buffer.constructor ? u : new buffer(u)).toString("base64");
        } : function(u) {
          return btoa(utob(u));
        };
        var encode = function encode(u, urisafe) {
          return urisafe ? _encode(String(u)).replace(/[+\/]/g, function(m0) {
            return "+" == m0 ? "-" : "_";
          }).replace(/=/g, "") : _encode(String(u));
        };
        var encodeURI = function encodeURI(u) {
          return encode(u, true);
        };
        var re_btou = new RegExp([ "[À-ß][-¿]", "[à-ï][-¿]{2}", "[ð-÷][-¿]{3}" ].join("|"), "g");
        var cb_btou = function cb_btou(cccc) {
          switch (cccc.length) {
           case 4:
            var cp = (7 & cccc.charCodeAt(0)) << 18 | (63 & cccc.charCodeAt(1)) << 12 | (63 & cccc.charCodeAt(2)) << 6 | 63 & cccc.charCodeAt(3), offset = cp - 65536;
            return fromCharCode(55296 + (offset >>> 10)) + fromCharCode(56320 + (1023 & offset));

           case 3:
            return fromCharCode((15 & cccc.charCodeAt(0)) << 12 | (63 & cccc.charCodeAt(1)) << 6 | 63 & cccc.charCodeAt(2));

           default:
            return fromCharCode((31 & cccc.charCodeAt(0)) << 6 | 63 & cccc.charCodeAt(1));
          }
        };
        var btou = function btou(b) {
          return b.replace(re_btou, cb_btou);
        };
        var cb_decode = function cb_decode(cccc) {
          var len = cccc.length, padlen = len % 4, n = (len > 0 ? b64tab[cccc.charAt(0)] << 18 : 0) | (len > 1 ? b64tab[cccc.charAt(1)] << 12 : 0) | (len > 2 ? b64tab[cccc.charAt(2)] << 6 : 0) | (len > 3 ? b64tab[cccc.charAt(3)] : 0), chars = [ fromCharCode(n >>> 16), fromCharCode(n >>> 8 & 255), fromCharCode(255 & n) ];
          chars.length -= [ 0, 0, 2, 1 ][padlen];
          return chars.join("");
        };
        var atob = global.atob ? function(a) {
          return global.atob(a);
        } : function(a) {
          return a.replace(/[\s\S]{1,4}/g, cb_decode);
        };
        var _decode = buffer ? buffer.from && buffer.from !== Uint8Array.from ? function(a) {
          return (a.constructor === buffer.constructor ? a : buffer.from(a, "base64")).toString();
        } : function(a) {
          return (a.constructor === buffer.constructor ? a : new buffer(a, "base64")).toString();
        } : function(a) {
          return btou(atob(a));
        };
        var decode = function decode(a) {
          return _decode(String(a).replace(/[-_]/g, function(m0) {
            return "-" == m0 ? "+" : "/";
          }).replace(/[^A-Za-z0-9\+\/]/g, ""));
        };
        var noConflict = function noConflict() {
          var Base64 = global.Base64;
          global.Base64 = _Base64;
          return Base64;
        };
        global.Base64 = {
          VERSION: version,
          atob: atob,
          btoa: btoa,
          fromBase64: decode,
          toBase64: encode,
          utob: utob,
          encode: encode,
          encodeURI: encodeURI,
          btou: btou,
          decode: decode,
          noConflict: noConflict
        };
        if ("function" === typeof Object.defineProperty) {
          var noEnum = function noEnum(v) {
            return {
              value: v,
              enumerable: false,
              writable: true,
              configurable: true
            };
          };
          global.Base64.extendString = function() {
            Object.defineProperty(String.prototype, "fromBase64", noEnum(function() {
              return decode(this);
            }));
            Object.defineProperty(String.prototype, "toBase64", noEnum(function(urisafe) {
              return encode(this, urisafe);
            }));
            Object.defineProperty(String.prototype, "toBase64URI", noEnum(function() {
              return encode(this, true);
            }));
          };
        }
        global["Meteor"] && (Base64 = global.Base64);
        "undefined" !== typeof module && module.exports ? module.exports.Base64 = global.Base64 : "function" === typeof define && define.amd && define([], function() {
          return global.Base64;
        });
        return {
          Base64: global.Base64
        };
      });
      cc._RF.pop();
    }).call(this, "undefined" !== typeof global ? global : "undefined" !== typeof self ? self : "undefined" !== typeof window ? window : {});
  }, {
    buffer: 2
  } ],
  config: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "2260bXFr3lFaamyrMj5Npeh", "config");
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var appConfig = {
      appUserInfo: {
        uuid: "pad_h8f513755vb55hyr203a3e3511f5fe90",
        userName: "ceshi001",
        phone: "13912623142",
        token: "d6d5c8497eaec6883eb9405bca074050",
        cpid: "84556418",
        location: "shanghai",
        sexModel: 1,
        imei: "Fkenf234232934",
        extra: "",
        photo: "",
        photoUrl: ""
      },
      appGameInfo: {
        gameId: "ENG-ZQ-001",
        gameName: "足 球",
        gameHost: "http://localhost:8090/api",
        gamePort: "8300",
        gameCDN: "http://103.41.123/54.188:35588",
        gameType: "1",
        gameKey: "woi92839249234j"
      },
      userData: null,
      HOST_URL: "/api",
      IMG_URL: "http://ftb.tmttg.com/",
      HELP_URL: "http://foot.tmttg.com/help/index"
    };
    window["appConfig"] = appConfig;
    exports.default = appConfig;
    cc._RF.pop();
  }, {} ],
  tableView: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "5ab7dAcwhhEgb/kKjREwN7r", "tableView");
    "use strict";
    var ScrollModel = cc.Enum({
      Horizontal: 0,
      Vertical: 1
    });
    var ScrollDirection = cc.Enum({
      None: 0,
      Up: 1,
      Down: 2,
      Left: 3,
      Rigth: 4
    });
    var Direction = cc.Enum({
      LEFT_TO_RIGHT__TOP_TO_BOTTOM: 0,
      TOP_TO_BOTTOM__LEFT_TO_RIGHT: 1
    });
    var ViewType = cc.Enum({
      Scroll: 0,
      Flip: 1
    });
    var _searchMaskParent = function _searchMaskParent(node) {
      var Mask = cc.Mask;
      if (Mask) {
        var index = 0;
        for (var curr = node; curr && cc.Node.isNode(curr); curr = curr._parent, ++index) if (curr.getComponent(Mask)) return {
          index: index,
          node: curr
        };
      }
      return null;
    };
    function quickSort(arr, cb) {
      if (arr.length <= 1) return arr;
      var pivotIndex = Math.floor(arr.length / 2);
      var pivot = arr[pivotIndex];
      var left = [];
      var right = [];
      for (var i = 0; i < arr.length; i++) i !== pivotIndex && (cb ? cb(arr[i], pivot) ? left.push(arr[i]) : right.push(arr[i]) : arr[i] <= pivot ? left.push(arr[i]) : right.push(arr[i]));
      return quickSort(left, cb).concat([ pivot ], quickSort(right, cb));
    }
    var tableView = cc.Class({
      extends: cc.ScrollView,
      editor: false,
      properties: {
        _data: null,
        _minCellIndex: 0,
        _maxCellIndex: 0,
        _paramCount: 0,
        _count: 0,
        _cellCount: 0,
        _showCellCount: 0,
        _groupCellCount: null,
        _scrollDirection: ScrollDirection.None,
        _cellPool: null,
        _view: null,
        _page: 0,
        _pageTotal: 0,
        _touchLayer: cc.Node,
        _loadSuccess: false,
        _initSuccess: false,
        _scheduleInit: false,
        spaceX: {
          default: 0
        },
        cell: {
          default: null,
          type: cc.Prefab,
          notify: function notify(oldValue) {}
        },
        ScrollModel: {
          default: 0,
          type: ScrollModel,
          notify: function notify(oldValue) {
            if (this.ScrollModel === ScrollModel.Horizontal) {
              this.horizontal = true;
              this.vertical = false;
              this.verticalScrollBar = null;
            } else {
              this.vertical = true;
              this.horizontal = false;
              this.horizontalScrollBar = null;
            }
          },
          tooltip: "横向纵向滑动"
        },
        ViewType: {
          default: 0,
          type: ViewType,
          notify: function notify(oldValue) {
            this.ViewType === ViewType.Flip ? this.inertia = false : this.inertia = true;
          },
          tooltip: "为Scroll时,不做解释\n为Flipw时，在Scroll的基础上增加翻页的行为"
        },
        isFill: {
          default: false,
          tooltip: "当节点不能铺满一页时，选择isFill为true会填充节点铺满整个view"
        },
        Direction: {
          default: 0,
          type: Direction,
          tooltip: "规定cell的排列方向"
        },
        pageChangeEvents: {
          default: [],
          type: cc.Component.EventHandler,
          tooltip: "仅当ViewType为pageView时有效，初始化或翻页时触发回调，向回调传入两个参数，参数一为当前处于哪一页，参数二为一共多少页"
        }
      },
      statics: {
        _cellPoolCache: {}
      },
      onLoad: function onLoad() {
        window.s = this;
        var self = this;
        tableView._tableView.push(this);
        var destroy = this.node.destroy;
        this.node.destroy = function() {
          self.clear();
          destroy.call(self.node);
        };
        var _onPreDestroy = this.node._onPreDestroy;
        this.node._onPreDestroy = function() {
          self.clear();
          _onPreDestroy.call(self.node);
        };
      },
      onDestroy: function onDestroy() {
        cc.eventManager.removeListener(this._touchListener);
        true;
        this._touchListener.release();
        for (var key in tableView._tableView) if (tableView._tableView[key] === this) {
          tableView._tableView.splice(key);
          return;
        }
      },
      _addListenerToTouchLayer: function _addListenerToTouchLayer() {
        this._touchLayer = new cc.Node();
        var widget = this._touchLayer.addComponent(cc.Widget);
        widget.isAlignTop = true;
        widget.isAlignBottom = true;
        widget.isAlignLeft = true;
        widget.isAlignRight = true;
        widget.top = 0;
        widget.bottom = 0;
        widget.left = 0;
        widget.right = 0;
        widget.isAlignOnce = false;
        this._touchLayer.parent = this._view;
        var self = this;
        this._touchListener = cc.EventListener.create({
          event: cc.EventListener.TOUCH_ONE_BY_ONE,
          swallowTouches: false,
          ower: this._touchLayer,
          mask: _searchMaskParent(this._touchLayer),
          onTouchBegan: function onTouchBegan(touch, event) {
            var pos = touch.getLocation();
            var node = this.ower;
            if (node._hitTest(pos, this)) {
              self._touchstart(touch);
              return true;
            }
            return false;
          },
          onTouchMoved: function onTouchMoved(touch, event) {
            self._touchmove(touch);
          },
          onTouchEnded: function onTouchEnded(touch, event) {
            self._touchend(touch);
          }
        });
        true;
        this._touchListener.retain();
        cc.eventManager.addListener(this._touchListener, this._touchLayer);
      },
      _setStopPropagation: function _setStopPropagation() {
        this.node.on("touchstart", function(event) {
          event.stopPropagation();
        });
        this.node.on("touchmove", function(event) {
          event.stopPropagation();
        });
        this.node.on("touchend", function(event) {
          event.stopPropagation();
        });
        this.node.on("touchcancel", function(event) {
          event.stopPropagation();
        });
      },
      _initCell: function _initCell(cell, reload) {
        if (this.ScrollModel === ScrollModel.Horizontal && this.Direction === Direction.TOP_TO_BOTTOM__LEFT_TO_RIGHT || this.ScrollModel === ScrollModel.Vertical && this.Direction === Direction.LEFT_TO_RIGHT__TOP_TO_BOTTOM) {
          var tag = cell.tag * cell.childrenCount;
          for (var index = 0; index < cell.childrenCount; ++index) {
            var node = cell.children[index];
            var viewCell = node.getComponent("TableViewCell");
            if (viewCell) {
              viewCell._cellInit_(this);
              viewCell.init(tag + index, this._data, reload, [ cell.tag, index ]);
            }
          }
        } else if (this.ViewType === ViewType.Flip) {
          var tag = Math.floor(cell.tag / this._showCellCount);
          var tagnum = tag * this._showCellCount * cell.childrenCount;
          for (var index = 0; index < cell.childrenCount; ++index) {
            var node = cell.children[index];
            var viewCell = node.getComponent("TableViewCell");
            if (viewCell) {
              viewCell._cellInit_(this);
              viewCell.init(this._showCellCount * index + cell.tag % this._showCellCount + tagnum, this._data, reload, [ index + tag * cell.childrenCount, index ]);
            }
          }
        } else for (var index = 0; index < cell.childrenCount; ++index) {
          var node = cell.children[index];
          var viewCell = node.getComponent("TableViewCell");
          if (viewCell) {
            viewCell._cellInit_(this);
            viewCell.init(index * this._count + cell.tag, this._data, reload, [ index, index ]);
          }
        }
      },
      _setCellPosition: function _setCellPosition(node, index) {
        if (this.ScrollModel === ScrollModel.Horizontal) {
          node.x = 0 === index ? -this.content.width * this.content.anchorX + node.width * node.anchorX : this.content.getChildByTag(index - 1).x + node.width;
          node.y = (node.anchorY - this.content.anchorY) * node.height;
        } else {
          node.y = 0 === index ? this.content.height * (1 - this.content.anchorY) - node.height * (1 - node.anchorY) : this.content.getChildByTag(index - 1).y - node.height;
          node.x = (node.anchorX - this.content.anchorX) * node.width;
        }
      },
      _addCell: function _addCell(index) {
        var cell = this._getCell();
        this._setCellAttr(cell, index);
        this._setCellPosition(cell, index);
        cell.parent = this.content;
        this._initCell(cell);
      },
      _setCellAttr: function _setCellAttr(cell, index) {
        cell.setSiblingIndex(index >= cell.tag ? this._cellCount : 0);
        cell.tag = index;
      },
      _addCellsToView: function _addCellsToView() {
        for (var index = 0; index <= this._maxCellIndex; ++index) this._addCell(index);
      },
      _getCell: function _getCell() {
        if (0 === this._cellPool.size()) {
          var cell = cc.instantiate(this.cell);
          var node = new cc.Node();
          node.anchorX = .5;
          node.anchorY = .5;
          var length = 0;
          if (this.ScrollModel === ScrollModel.Horizontal) {
            node.width = cell.width;
            var childrenCount = Math.floor(this.content.height / cell.height);
            node.height = this.content.height;
            for (var index = 0; index < childrenCount; ++index) {
              cell || (cell = cc.instantiate(this.cell));
              cell.x = (cell.anchorX - .5) * cell.width;
              cell.y = node.height / 2 - cell.height * (1 - cell.anchorY) - length;
              length += cell.height;
              cell.parent = node;
              cell = null;
            }
          } else {
            node.height = cell.height;
            var childrenCount = Math.floor(this.content.width / cell.width);
            node.width = this.content.width;
            for (var index = 0; index < childrenCount; ++index) {
              cell || (cell = cc.instantiate(this.cell));
              cell.y = (cell.anchorY - .5) * cell.height;
              cell.x = index % 2 == 0 ? -node.width / 2 + cell.width * cell.anchorX + length : -node.width / 2 + this.spaceX + cell.width * cell.anchorX + length;
              length += cell.width;
              cell.parent = node;
              cell = null;
            }
          }
          this._cellPool.put(node);
        }
        var cell = this._cellPool.get();
        return cell;
      },
      _getCellSize: function _getCellSize() {
        var cell = this._getCell();
        var cellSize = cell.getContentSize();
        this._cellPool.put(cell);
        return cellSize;
      },
      _getGroupCellCount: function _getGroupCellCount() {
        var cell = this._getCell();
        var groupCellCount = cell.childrenCount;
        this._cellPool.put(cell);
        return groupCellCount;
      },
      clear: function clear() {
        for (var index = this.content.childrenCount - 1; index >= 0; --index) this._cellPool.put(this.content.children[index]);
        this._cellCount = 0;
        this._showCellCount = 0;
      },
      reload: function reload(data) {
        void 0 !== data && (this._data = data);
        for (var index = this.content.childrenCount - 1; index >= 0; --index) this._initCell(this.content.children[index], true);
      },
      _getCellPoolCacheName: function _getCellPoolCacheName() {
        return this.ScrollModel === ScrollModel.Horizontal ? this.cell.name + "h" + this.content.height : this.cell.name + "w" + this.content.width;
      },
      _initTableView: function _initTableView() {
        this._scheduleInit = false;
        this._cellPool && this.clear();
        var name = this._getCellPoolCacheName();
        tableView._cellPoolCache[name] || (tableView._cellPoolCache[name] = new cc.NodePool("TableViewCell"));
        this._cellPool = tableView._cellPoolCache[name];
        this._cellSize = this._getCellSize();
        this._groupCellCount = this._getGroupCellCount();
        this._count = Math.ceil(this._paramCount / this._groupCellCount);
        if (this.ScrollModel === ScrollModel.Horizontal) {
          this._view.width = this.node.width;
          this._view.x = (this._view.anchorX - this.node.anchorX) * this._view.width;
          this._cellCount = Math.ceil(this._view.width / this._cellSize.width) + 1;
          if (this.ViewType === ViewType.Flip) if (this._cellCount > this._count) {
            this.isFill ? this._cellCount = Math.floor(this._view.width / this._cellSize.width) : this._cellCount = this._count;
            this._showCellCount = this._cellCount;
            this._pageTotal = 1;
          } else {
            this._pageTotal = Math.ceil(this._count / (this._cellCount - 1));
            this._count = this._pageTotal * (this._cellCount - 1);
            this._showCellCount = this._cellCount - 1;
          } else if (this._cellCount > this._count) {
            this.isFill ? this._cellCount = Math.floor(this._view.width / this._cellSize.width) : this._cellCount = this._count;
            this._showCellCount = this._cellCount;
          } else this._showCellCount = this._cellCount - 1;
          this.content.width = this._count * this._cellSize.width;
          this.stopAutoScroll();
          this.scrollToLeft();
        } else {
          this._view.height = this.node.height;
          this._view.y = (this._view.anchorY - this.node.anchorY) * this._view.height;
          this._cellCount = Math.ceil(this._view.height / this._cellSize.height) + 1;
          if (this.ViewType === ViewType.Flip) if (this._cellCount > this._count) {
            this.isFill ? this._cellCount = Math.floor(this._view.height / this._cellSize.height) : this._cellCount = this._count;
            this._showCellCount = this._cellCount;
            this._pageTotal = 1;
          } else {
            this._pageTotal = Math.ceil(this._count / (this._cellCount - 1));
            this._count = this._pageTotal * (this._cellCount - 1);
            this._showCellCount = this._cellCount - 1;
          } else if (this._cellCount > this._count) {
            this.isFill ? this._cellCount = Math.floor(this._view.height / this._cellSize.height) : this._cellCount = this._count;
            this._showCellCount = this._cellCount;
          } else this._showCellCount = this._cellCount - 1;
          this.content.height = this._count * this._cellSize.height;
          this.stopAutoScroll();
          this.scrollToTop();
        }
        this._changePageNum(1 - this._page);
        this._lastOffset = this.getScrollOffset();
        this._minCellIndex = 0;
        this._maxCellIndex = this._cellCount - 1;
        this._addCellsToView();
        this._initSuccess = true;
      },
      initTableView: function initTableView(paramCount, data) {
        this._paramCount = paramCount;
        this._data = data;
        if (this._loadSuccess) this._scheduleInit || this._initTableView(); else {
          if (this.ScrollModel === ScrollModel.Horizontal) {
            this.horizontal = true;
            this.vertical = false;
          } else {
            this.vertical = true;
            this.horizontal = false;
          }
          this._view = this.content.parent;
          this.verticalScrollBar && this.verticalScrollBar.node.on("size-changed", function() {
            this._updateScrollBar(this._getHowMuchOutOfBoundary());
          }, this);
          this.horizontalScrollBar && this.horizontalScrollBar.node.on("size-changed", function() {
            this._updateScrollBar(this._getHowMuchOutOfBoundary());
          }, this);
          this._addListenerToTouchLayer();
          this._setStopPropagation();
          if (this.node.getComponent(cc.Widget) || this._view.getComponent(cc.Widget) || this.content.getComponent(cc.Widget)) {
            this.scheduleOnce(this._initTableView);
            this._scheduleInit = true;
          } else this._initTableView();
          this._loadSuccess = true;
        }
      },
      stopAutoScroll: function stopAutoScroll() {
        if (this._scheduleInit) {
          this.scheduleOnce(function() {
            this.stopAutoScroll();
          });
          return;
        }
        this._scrollDirection = ScrollDirection.None;
        this._super();
      },
      scrollToBottom: function scrollToBottom(timeInSecond, attenuated) {
        if (this._scheduleInit) {
          this.scheduleOnce(function() {
            this.scrollToBottom(timeInSecond, attenuated);
          });
          return;
        }
        this._scrollDirection = ScrollDirection.Up;
        this._super(timeInSecond, attenuated);
      },
      scrollToTop: function scrollToTop(timeInSecond, attenuated) {
        if (this._scheduleInit) {
          this.scheduleOnce(function() {
            this.scrollToTop(timeInSecond, attenuated);
          });
          return;
        }
        this._scrollDirection = ScrollDirection.Down;
        this._super(timeInSecond, attenuated);
      },
      scrollToLeft: function scrollToLeft(timeInSecond, attenuated) {
        if (this._scheduleInit) {
          this.scheduleOnce(function() {
            this.scrollToLeft(timeInSecond, attenuated);
          });
          return;
        }
        this._scrollDirection = ScrollDirection.Rigth;
        this._super(timeInSecond, attenuated);
      },
      scrollToRight: function scrollToRight(timeInSecond, attenuated) {
        if (this._scheduleInit) {
          this.scheduleOnce(function() {
            this.scrollToRight(timeInSecond, attenuated);
          });
          return;
        }
        this._scrollDirection = ScrollDirection.Left;
        this._super(timeInSecond, attenuated);
      },
      scrollToOffset: function scrollToOffset(offset, timeInSecond, attenuated) {
        if (this._scheduleInit) {
          this.scheduleOnce(function() {
            this.scrollToOffset(offset, timeInSecond, attenuated);
          });
          return;
        }
        var nowoffset = this.getScrollOffset();
        var p = cc.pSub(offset, nowoffset);
        this.ScrollModel === ScrollModel.Horizontal ? p.x > 0 ? this._scrollDirection = ScrollDirection.Left : p.x < 0 && (this._scrollDirection = ScrollDirection.Rigth) : p.y > 0 ? this._scrollDirection = ScrollDirection.Up : p.y < 0 && (this._scrollDirection = ScrollDirection.Down);
        this._super(offset, timeInSecond, attenuated);
      },
      addScrollEvent: function addScrollEvent(target, component, handler) {
        var eventHandler = new cc.Component.EventHandler();
        eventHandler.target = target;
        eventHandler.component = component;
        eventHandler.handler = handler;
        this.scrollEvents.push(eventHandler);
      },
      removeScrollEvent: function removeScrollEvent(target) {
        for (var key in this.scrollEvents) {
          var eventHandler = this.scrollEvents[key];
          if (eventHandler.target === target) {
            this.scrollEvents.splice(key, 1);
            return;
          }
        }
      },
      clearScrollEvent: function clearScrollEvent() {
        this.scrollEvents = [];
      },
      addPageEvent: function addPageEvent(target, component, handler) {
        var eventHandler = new cc.Component.EventHandler();
        eventHandler.target = target;
        eventHandler.component = component;
        eventHandler.handler = handler;
        this.pageChangeEvents.push(eventHandler);
      },
      removePageEvent: function removePageEvent(target) {
        for (var key in this.pageChangeEvents) {
          var eventHandler = this.pageChangeEvents[key];
          if (eventHandler.target === target) {
            this.pageChangeEvents.splice(key, 1);
            return;
          }
        }
      },
      clearPageEvent: function clearPageEvent() {
        this.pageChangeEvents = [];
      },
      scrollToNextPage: function scrollToNextPage() {
        this.scrollToPage(this._page + 1);
      },
      scrollToLastPage: function scrollToLastPage() {
        this.scrollToPage(this._page - 1);
      },
      scrollToPage: function scrollToPage(page) {
        if (this.ViewType !== ViewType.Flip || page === this._page) return;
        if (page < 1 || page > this._pageTotal) return;
        var time = .3 * Math.abs(page - this._page);
        this._changePageNum(page - this._page);
        if (this._initSuccess) {
          var x = this._view.width;
          var y = this._view.height;
          x = (this._page - 1) * x;
          y = (this._page - 1) * y;
          this.scrollToOffset({
            x: x,
            y: y
          }, time);
        } else this.scheduleOnce(function() {
          var x = this._view.width;
          var y = this._view.height;
          x = (this._page - 1) * x;
          y = (this._page - 1) * y;
          this.scrollToOffset({
            x: x,
            y: y
          }, time);
        });
      },
      getCells: function getCells(callback) {
        var self = this;
        var f = function f() {
          var cells = [];
          var nodes = quickSort(self.content.children, function(a, b) {
            return a.tag < b.tag;
          });
          for (var key in nodes) {
            var node = nodes[key];
            for (var k in node.children) cells.push(node.children[k]);
          }
          callback(cells);
        };
        this._initSuccess ? f() : this.scheduleOnce(f);
      },
      getData: function getData() {
        return this._data;
      },
      getGroupsRange: function getGroupsRange(callback) {
        var self = this;
        var f = function f() {
          var arr = [];
          for (var i = self._minCellIndex; i <= self._maxCellIndex; i++) arr.push(i);
          callback(arr);
        };
        this._initSuccess ? f() : this.scheduleOnce(f);
      },
      _changePageNum: function _changePageNum(num) {
        this._page += num;
        this._page <= 0 ? this._page = 1 : this._page > this._pageTotal && (this._page = this._pageTotal);
        for (var key in this.pageChangeEvents) {
          var event = this.pageChangeEvents[key];
          event.emit([ this._page, this._pageTotal ]);
        }
      },
      _touchstart: function _touchstart(event) {
        this.ScrollModel === ScrollModel.Horizontal ? this.horizontal = false : this.vertical = false;
      },
      _touchmove: function _touchmove(event) {
        if (this.horizontal === this.vertical) {
          var startL = event.getStartLocation();
          var l = event.getLocation();
          if (this.ScrollModel === ScrollModel.Horizontal) {
            if (Math.abs(l.x - startL.x) <= 7) return;
          } else if (Math.abs(l.y - startL.y) <= 7) return;
          this.ScrollModel === ScrollModel.Horizontal ? this.horizontal = true : this.vertical = true;
        }
      },
      _touchend: function _touchend(event) {
        this.ScrollModel === ScrollModel.Horizontal ? this.horizontal = true : this.vertical = true;
        this.ViewType === ViewType.Flip && this._pageTotal > 1 && this._pageMove(event);
      },
      _pageMove: function _pageMove(event) {
        var x = this._view.width;
        var y = this._view.height;
        if (this.ViewType === ViewType.Flip) {
          var offset = this.getScrollOffset();
          var offsetMax = this.getMaxScrollOffset();
          if (this.ScrollModel === ScrollModel.Horizontal) {
            if (offset.x >= 0 || offset.x <= -offsetMax.x) return;
            y = 0;
            if (Math.abs(event.getLocation().x - event.getStartLocation().x) > this._view.width / 4) if (this._scrollDirection === ScrollDirection.Left) {
              if (!(this._page < this._pageTotal)) return;
              this._changePageNum(1);
            } else if (this._scrollDirection === ScrollDirection.Rigth) {
              if (!(this._page > 1)) return;
              this._changePageNum(-1);
            }
          } else {
            if (offset.y >= offsetMax.y || offset.y <= 0) return;
            x = 0;
            if (Math.abs(event.getLocation().y - event.getStartLocation().y) > this._view.height / 4) if (this._scrollDirection === ScrollDirection.Up) {
              if (!(this._page < this._pageTotal)) return;
              this._changePageNum(1);
            } else if (this._scrollDirection === ScrollDirection.Down) {
              if (!(this._page > 1)) return;
              this._changePageNum(-1);
            }
          }
          x = (this._page - 1) * x;
          y = (this._page - 1) * y;
          this.scrollToOffset({
            x: x,
            y: y
          }, .3);
        }
      },
      _getBoundingBoxToWorld: function _getBoundingBoxToWorld(node) {
        var p = node.convertToWorldSpace(cc.p(0, 0));
        return cc.rect(p.x, p.y, node.width, node.height);
      },
      _updateCells: function _updateCells() {
        if (this.ScrollModel === ScrollModel.Horizontal) {
          if (this._scrollDirection === ScrollDirection.Left) {
            if (this._maxCellIndex < this._count - 1) {
              var viewBox = this._getBoundingBoxToWorld(this._view);
              do {
                var node = this.content.getChildByTag(this._minCellIndex);
                var nodeBox = this._getBoundingBoxToWorld(node);
                if (!(nodeBox.xMax <= viewBox.xMin)) break;
                node.x = this.content.getChildByTag(this._maxCellIndex).x + node.width;
                this._minCellIndex++;
                this._maxCellIndex++;
                if (nodeBox.xMax + (this._maxCellIndex - this._minCellIndex + 1) * node.width > viewBox.xMin) {
                  this._setCellAttr(node, this._maxCellIndex);
                  this._initCell(node);
                }
              } while (this._maxCellIndex !== this._count - 1);
            }
          } else if (this._scrollDirection === ScrollDirection.Rigth && this._minCellIndex > 0) {
            var viewBox = this._getBoundingBoxToWorld(this._view);
            do {
              var node = this.content.getChildByTag(this._maxCellIndex);
              var nodeBox = this._getBoundingBoxToWorld(node);
              if (!(nodeBox.xMin >= viewBox.xMax)) break;
              node.x = this.content.getChildByTag(this._minCellIndex).x - node.width;
              this._minCellIndex--;
              this._maxCellIndex--;
              if (nodeBox.xMin - (this._maxCellIndex - this._minCellIndex + 1) * node.width < viewBox.xMax) {
                this._setCellAttr(node, this._minCellIndex);
                this._initCell(node);
              }
            } while (0 !== this._minCellIndex);
          }
        } else if (this._scrollDirection === ScrollDirection.Up) {
          if (this._maxCellIndex < this._count - 1) {
            var viewBox = this._getBoundingBoxToWorld(this._view);
            do {
              var node = this.content.getChildByTag(this._minCellIndex);
              var nodeBox = this._getBoundingBoxToWorld(node);
              if (!(nodeBox.yMin >= viewBox.yMax)) break;
              node.y = this.content.getChildByTag(this._maxCellIndex).y - node.height;
              this._minCellIndex++;
              this._maxCellIndex++;
              if (nodeBox.yMin - (this._maxCellIndex - this._minCellIndex + 1) * node.height < viewBox.yMax) {
                this._setCellAttr(node, this._maxCellIndex);
                this._initCell(node);
              }
            } while (this._maxCellIndex !== this._count - 1);
          }
        } else if (this._scrollDirection === ScrollDirection.Down && this._minCellIndex > 0) {
          var viewBox = this._getBoundingBoxToWorld(this._view);
          do {
            var node = this.content.getChildByTag(this._maxCellIndex);
            var nodeBox = this._getBoundingBoxToWorld(node);
            if (!(nodeBox.yMax <= viewBox.yMin)) break;
            node.y = this.content.getChildByTag(this._minCellIndex).y + node.height;
            this._minCellIndex--;
            this._maxCellIndex--;
            if (nodeBox.yMax + (this._maxCellIndex - this._minCellIndex + 1) * node.width > viewBox.yMin) {
              this._setCellAttr(node, this._minCellIndex);
              this._initCell(node);
            }
          } while (0 !== this._minCellIndex);
        }
      },
      _getScrollDirection: function _getScrollDirection() {
        var offset = this.getScrollOffset();
        var lastOffset = this._lastOffset;
        this._lastOffset = offset;
        offset = cc.pSub(offset, lastOffset);
        this.ScrollModel === ScrollModel.Horizontal ? offset.x > 0 ? this._scrollDirection = ScrollDirection.Rigth : offset.x < 0 ? this._scrollDirection = ScrollDirection.Left : this._scrollDirection = ScrollDirection.None : offset.y < 0 ? this._scrollDirection = ScrollDirection.Down : offset.y > 0 ? this._scrollDirection = ScrollDirection.Up : this._scrollDirection = ScrollDirection.None;
      },
      lastUpdate: 0,
      update: function update(dt) {
        this._super(dt);
        if (!this._initSuccess || this._cellCount === this._showCellCount || 1 === this._pageTotal) return;
        console.log(this.lastUpdate);
        this._getScrollDirection();
        this._updateCells();
      }
    });
    tableView._tableView = [];
    tableView.reload = function() {
      for (var key in tableView._tableView) tableView._tableView[key].reload();
    };
    tableView.clear = function() {
      for (var key in tableView._tableView) tableView._tableView[key].clear();
    };
    cc.tableView = module.export = tableView;
    cc._RF.pop();
  }, {} ],
  viewCell: [ function(require, module, exports) {
    "use strict";
    cc._RF.push(module, "7f2acwzam1MwobbG7ZQrprK", "viewCell");
    "use strict";
    Object.defineProperty(exports, "__esModule", {
      value: true
    });
    var ViewCell = cc.Class({
      extends: cc.Component,
      properties: {
        tableView: {
          default: null,
          visible: false
        },
        _isCellInit_: false,
        _longClicked_: false
      },
      _cellAddMethodToNode_: function _cellAddMethodToNode_() {
        this.node.clicked = this.clicked.bind(this);
      },
      _cellAddTouch_: function _cellAddTouch_() {
        this.node.on(cc.Node.EventType.TOUCH_START, function(event) {
          if (true === this.node.active && 0 !== this.node.opacity && !this._longClicked_) {
            this._longClicked_ = true;
            this.scheduleOnce(this._longClicked, 1.5);
          }
        }, this);
        this.node.on(cc.Node.EventType.TOUCH_MOVE, function() {
          if (this._longClicked_) {
            this._longClicked_ = false;
            this.unschedule(this._longClicked);
          }
        }, this);
        this.node.on(cc.Node.EventType.TOUCH_END, function() {
          this.clicked();
          if (this._longClicked_) {
            this._longClicked_ = false;
            this.unschedule(this._longClicked);
          }
        }, this);
        this.node.on(cc.Node.EventType.TOUCH_CANCEL, function() {
          if (this._longClicked_) {
            this._longClicked_ = false;
            this.unschedule(this._longClicked);
          }
        }, this);
      },
      _cellInit_: function _cellInit_(tableView) {
        this.tableView = tableView;
        if (!this._isCellInit_) {
          this._cellAddMethodToNode_();
          this._cellAddTouch_();
          this._isCellInit_ = true;
        }
      },
      _longClicked: function _longClicked() {
        this._longClicked_ = false;
        this.node.emit(cc.Node.EventType.TOUCH_CANCEL);
        this.longClicked();
      },
      longClicked: function longClicked() {},
      clicked: function clicked() {},
      init: function init(index, data, reload, group) {}
    });
    exports.default = ViewCell;
    module.exports = exports["default"];
    cc._RF.pop();
  }, {} ]
}, {}, [ "AppData", "AppGame", "Loading", "NodeCaches", "BuyAlert", "BuyItemDelegate", "HelpAlert", "HelpTabContent", "KeyBoard", "LoadMore", "LogItemDelegate", "LogListAlert", "NumberInput", "SearchItemDelegate", "SearchListAlert", "TipsAlert", "config", "ChooseItemType1Delegate", "ChooseItemType2Delegate", "ChooseItemType3Delegate", "ChooseItemType4Delegate", "ChooseItemType5Delegate", "GameItemDelegate", "GamePageItemDelegate", "MenuScrollDelegate", "SectionGridItemDelegate", "SectionItemDelegate", "SectionRowDelegate", "SectionScrollDelegate", "ChooseItemModel", "GameItemModel", "SectionGridItem", "TeamModel", "base64", "MenuItemDelegate", "MenuListScrollDelegate", "MenuModel", "TableViewCell", "tableView", "viewCell", "ApiNet", "BuyUtils", "DateUtils", "Helper", "HotUpdate", "AppPageView", "AudioButton", "ListItemDelegate", "ListView", "NoticeBar", "RecycleItemDelegate", "RecycleView", "StateView", "Tab", "TabLayout" ]);
//# sourceMappingURL=project.dev.js.map