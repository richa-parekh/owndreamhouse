!(function (e, t) {
    window.aafe = window.aafe || {};

    aafe.translate = function (e, t) {
        return elementorCommon.translate(e, null, t, aafeEditor.i18n);
    }

    var i = {
        LibraryViews: {},
        LibraryModels: {},
        LibraryCollections: {},
        LibraryBehaviors: {},
        LibraryLayout: null,
        LibraryManager: null
    };
    (i.LibraryModels.Template = Backbone.Model.extend({
        defaults: {
            template_id: 0,
            title: "",
            type: "",
            thumbnail: "",
            url: "",
            isPro: !1,
            category: []
        }
    })),
    (i.LibraryCollections.Template = Backbone.Collection.extend({
        model: i.LibraryModels.Template
    })),
    (i.LibraryBehaviors.InsertTemplate = Marionette.Behavior.extend({
        ui: {
            insertButton: ".athemes-addons-templateLibrary-insert-button"
        },
        events: {
            "click @ui.insertButton": "onInsertButtonClick"
        },
        onInsertButtonClick: function () {
            aafe.library.insertTemplate({
                model: this.view.model
            });
        },
    })),
    (i.LibraryViews.Loading = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-loading",
        id: "athemes-addons-templateLibrary-loading"
    })),
    (i.LibraryViews.Logo = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-header-logo",
        className: "athemes-addons-templateLibrary-header-logo",
        templateHelpers: function () {
            return {
                title: this.getOption("title")
            };
        },
    })),
    (i.LibraryViews.BackButton = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-header-back",
        id: "elementor-template-library-header-preview-back",
        className: "athemes-addons-templateLibrary-header-back",
        events: function () {
            return {
                click: "onClick"
            };
        },
        onClick: function () {
            aafe.library.showTemplatesView();
        },
    })),
    (i.LibraryViews.Menu = Marionette.ItemView.extend({
        template: "#template-athemes-addons-TemplateLibrary_header-menu",
        id: "elementor-template-library-header-menu",
        className: "athemes-addons-TemplateLibrary_header-menu",
        templateHelpers: function () {
            return aafe.library.getTabs();
        },
        ui: { menuItem: ".elementor-template-library-menu-item" },
        events: { "click @ui.menuItem": "onMenuItemClick" },
        onMenuItemClick: function (e) {
            aafe.library.setFilter("category", ""),
            aafe.library.setFilter("text", ""),
            aafe.library.setFilter("pro", ""),
            aafe.library.setFilter("type", e.currentTarget.dataset.tab, !0),
            aafe.library.showTemplatesView();
        },
    })),
    (i.LibraryViews.EmptyTemplateCollection = Marionette.ItemView.extend({
        id: "elementor-template-library-templates-empty",
        template: "#template-athemes-addons-templateLibrary-empty",
        ui: {
            title: ".elementor-template-library-blank-title",
            message: ".elementor-template-library-blank-message"
        },
        modesStrings: {
            empty: {
                title: aafe.translate("templatesEmptyTitle"),
                message: aafe.translate("templatesEmptyMessage")
            },
            noResults: {
                title: aafe.translate("templatesNoResultsTitle"),
                message: aafe.translate("templatesNoResultsMessage")
            },
        },
        getCurrentMode: function () {
            return aafe.library.getFilter("text") ? "noResults" : "empty";
        },
        onRender: function () {
            var e = this.modesStrings[this.getCurrentMode()];
            this.ui.title.html(e.title), this.ui.message.html(e.message);
        },
    })),
    (i.LibraryViews.Actions = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-header-actions",
        id: "elementor-template-library-header-actions",
        ui: {
            sync: "#athemes-addons-templateLibrary-header-sync i"
        },
        events: {
            "click @ui.sync": "onSyncClick"
        },
        onSyncClick: function () {
            var e = this;
            e.ui.sync.addClass("eicon-animation-spin"),
                aafe.library.requestLibraryData({
                    onUpdate: function () {
                        e.ui.sync.removeClass("eicon-animation-spin"), aafe.library.updateBlocksView();
                    },
                    forceUpdate: !0,
                    forceSync: !0,
                });
        },
    })),
    (i.LibraryViews.InsertWrapper = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-header-insert",
        id: "elementor-template-library-header-preview",
        behaviors: {
            insertTemplate: {
                behaviorClass: i.LibraryBehaviors.InsertTemplate
            }
        },
    })),
    (i.LibraryViews.Preview = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-preview",
        className: "athemes-addons-templateLibrary-preview",
        ui: function () {
            return {
                iframe: "> iframe"
            };
        },
        onRender: function () {
            this.ui.iframe.attr("src", this.getOption("url")).hide();
            var e = this,
                t = new i.LibraryViews.Loading().render();
            this.$el.append(t.el),
                this.ui.iframe.on("load", function () {
                    e.$el.find("#athemes-addons-templateLibrary-loading").remove(), e.ui.iframe.show();
                });
        },
    })),
    (i.LibraryViews.TemplateCollection = Marionette.CompositeView.extend({
        template: "#template-athemes-addons-templateLibrary-templates",
        id: "athemes-addons-templateLibrary-templates",
        childViewContainer: "#athemes-addons-templateLibrary-templates-list",
        emptyView: function () {
            return new i.LibraryViews.EmptyTemplateCollection();
        },
        ui: {
            templatesWindow: ".athemes-addons-templateLibrary-templates-window",
            textFilter: "#athemes-addons-templateLibrary-search",
            categoryFilter: "#athemes-addons-templateLibrary-filter-category",
            proFilter: "#athemes-addons-templateLibrary-filter-pro",
            filterBar: "#athemes-addons-templateLibrary-toolbar-filter"
        },
        events: {
            "input @ui.textFilter": "onTextFilterInput",
            "change @ui.categoryFilter": "onCategoryFilterClick",
            "change @ui.proFilter": "onProFilterClick"
        },
        getChildView: function (e) {
            return i.LibraryViews.Template;
        },
        initialize: function () {
            this.listenTo(aafe.library.channels.templates, "filter:change", this._renderChildren);
        },
        filter: function (e) {
            var t = aafe.library.getFilterTerms(),
                i = !0;
            return (
                _.each(t, function (t, a) {
                    var n = aafe.library.getFilter(a);
                    if (n && t.callback) {
                        var r = t.callback.call(e, n);
                        return r || (i = !1), r;
                    }
                }),
                i
            );
        },
        setMasonrySkin: function () {
            var e = new elementorModules.utils.Masonry({
                container: this.$childViewContainer,
                items: this.$childViewContainer.children()
            });
            this.$childViewContainer.imagesLoaded(e.run.bind(e));
        },
        onRenderCollection: function () {
            this.setMasonrySkin(), this.updatePerfectScrollbar();
        },
        onTextFilterInput: function () {
            var e = this;
            _.defer(function () {
                aafe.library.setFilter("text", e.ui.textFilter.val());
            });
        },
        onCategoryFilterClick: function (t) {
            var i = t.currentTarget.selectedOptions[0].value;
            aafe.library.setFilter("category", i);
        },
        onProFilterClick: function (t) {
            aafe.library.setFilter("pro", t.currentTarget.value);
        },
        updatePerfectScrollbar: function () {
            this.perfectScrollbar || (this.perfectScrollbar = new PerfectScrollbar(this.ui.templatesWindow[0], {
                suppressScrollX: !0
            })), (this.perfectScrollbar.isRtl = !1), this.perfectScrollbar.update();
        },
        onRender: function () {
            this.$("#athemes-addons-templateLibrary-filter-category").select2({ placeholder: "Filter", allowClear: !0, width: 200 }), this.updatePerfectScrollbar();
        },
    })),
    (i.LibraryViews.Template = Marionette.ItemView.extend({
        template: "#template-athemes-addons-templateLibrary-template",
        className: "athemes-addons-templateLibrary-template",
        ui: {
            previewButton: ".athemes-addons-templateLibrary-preview-button, .athemes-addons-templateLibrary-template-preview"
        },
        events: {
            "click @ui.previewButton": "onPreviewButtonClick"
        },
        behaviors: {
            insertTemplate: {
                behaviorClass: i.LibraryBehaviors.InsertTemplate
            }
        },
        onPreviewButtonClick: function () {
            aafe.library.showPreviewView(this.model);
        },
    })),
    (i.Modal = elementorModules.common.views.modal.Layout.extend({
        escHtml: function ( str ) {
            return String( str )
                .replace( /&/g, '&amp;' )
                .replace( /</g, '&lt;' )
                .replace( />/g, '&gt;' )
                .replace( /"/g, '&quot;' );
        },
        getModalOptions: function () {
            return {
                id: "aafeTemplateLibraryModal",
                hide: {
                    onOutsideClick: !1,
                    onEscKeyPress: !0,
                    onBackgroundClick: !1
                }
            };
        },
        widgetSlugToLabel: function ( slug ) {
            return slug
                .replace( 'athemes-addons-', '' )
                .replace( /-/g, ' ' )
                .replace( /\b\w/g, function ( c ) { return c.toUpperCase(); } );
        },

        getMissingWidgets: function ( templateId ) {
            var requiredWidgets = aafeEditor.requiredWidgets || {};
            var inactiveWidgets = aafeEditor.inactiveWidgets || [];
            var required = requiredWidgets[ templateId ] || [];
            return required.filter( function ( widget ) {
                return inactiveWidgets.indexOf( widget ) !== -1;
            } );
        },

        buildDisabledButton: function ( missingWidgets ) {
            var self = this;
            var widgetLabels = missingWidgets.map( function ( w ) {
                return '<li>' + self.escHtml( self.widgetSlugToLabel( w ) ) + '</li>';
            } ).join( '' );

            return '<div class="athemes-addons-templateLibrary-disabled-wrap">' +
                '<span class="elementor-template-library-template-action athemes-addons-templateLibrary-disabled-button" aria-disabled="true">' +
                    '<i class="eicon-file-download" aria-hidden="true"></i>' +
                    '<span class="elementor-button-title">' + aafeEditor.i18n.insertLabel + '</span>' +
                '</span>' +
                '<div class="athemes-addons-missing-widgets-tooltip">' +
                    '<span class="athemes-addons-missing-widgets-label">' + aafeEditor.i18n.missingWidgetsLabel + '</span>' +
                    '<ul class="athemes-addons-missing-widgets-list">' + widgetLabels + '</ul>' +
                    ( aafeEditor.dashboardUrl ? '<a href="' + aafeEditor.dashboardUrl + '" target="_blank" rel="noopener noreferrer" class="athemes-addons-dashboard-link">' + aafeEditor.i18n.enableWidgetsDashboard + '</a>' : '' ) +
                '</div>' +
            '</div>';
        },

        getTemplateActionButton: function (e) {
            var missingWidgets = this.getMissingWidgets( e.template_id );
            if ( missingWidgets.length > 0 ) {
                return this.buildDisabledButton( missingWidgets );
            }
            var t = e.isPro && !aafeEditor.isProActive ? "pro-button" : "insert-button";
            var viewId = "#template-athemes-addons-templateLibrary-" + t;
            var template = Marionette.TemplateCache.get( viewId );
            return Marionette.Renderer.render( template );
        },

        proLabel: function (e) {
            var t = e.isPro && !aafeEditor.isProActive ? "<div class='pro-label'>Pro</div>" : "";
            return t;
        },

        showLogo: function (e) {
            this.getHeaderView().logoArea.show(new i.LibraryViews.Logo(e));
        },
        showDefaultHeader: function () {
            this.showLogo({
                title: "aafe"
            });
            var e = this.getHeaderView();
            e.tools.show(new i.LibraryViews.Actions()), e.menuArea.show(new i.LibraryViews.Menu());
        },
        showPreviewView: function (e) {
            var t = this.getHeaderView();
            t.logoArea.show(new i.LibraryViews.BackButton()), t.tools.show(new i.LibraryViews.InsertWrapper({
                model: e
            })), 
            this.modalContent.show(new i.LibraryViews.Preview({
                url: e.get("url")
            }));
        },
        showBlocksView: function (e) {
            this.modalContent.show(new i.LibraryViews.TemplateCollection({
                collection: e
            }));
        },
        showTemplatesView: function (e) {
            this.showDefaultHeader(), this.modalContent.show(new i.LibraryViews.TemplateCollection({ collection: e }));
        },
    })),
    (i.LibraryManager = function () {
        function a() {
            var n = e(this).closest(".elementor-top-section"),
                i = n.data("id"),
                p = t.documents.getCurrent().container.children,
                r = n.prev(".elementor-add-section");
           p &&
                _.each(p, function (e, t) {
                    i === e.cid && (m.atIndex = t);
                }),
                r.find(".elementor-add-aafe-button").length || r.find(FIND_SELECTOR).before($aafeLibraryButton);
        }

        function n(e) {
            var t = e.find(FIND_SELECTOR);
            t.length && !e.find(".elementor-add-aafe-button").length && t.before($aafeLibraryButton), e.on("click.onAddElement", ".elementor-editor-section-settings .elementor-editor-element-add", a);
        }
        function r(t, i) {
            i.addClass("elementor-active").siblings().removeClass("elementor-active");
        }
        function o() {
            var e = window.elementor.$previewContents,
                t = setInterval(function () {
                    n(e), e.find(".elementor-add-new-section").length > 0 && clearInterval(t);
                }, 100);
            e.on("click.onAddTemplateButton", ".elementor-add-aafe-button", m.showModal.bind(m)), this.channels.tabs.on("change:device", r);
        }
        var l,
            s,
            d,
            src,
            c,
            m = this;
        (FIND_SELECTOR = ".elementor-add-new-section .elementor-add-section-drag-title"),
        ($aafeLibraryButton = '<div class="elementor-add-section-area-button elementor-add-aafe-button"><svg width="68" height="57" viewBox="0 0 68 57" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.427 23.0259L0 56.1389H9.29972H20.4594L31.4597 56.1389L16.427 23.0259ZM27.3635 0L18.406 19.1546L35.7109 56.1389H53.6296L27.3635 0Z" fill="#4769D8"/><path d="M68 0H30.9375L38.5488 16.3456H68V0Z" fill="#4769D8"/><path d="M68 39.7189H49.5295L57.1409 56.1389H68V39.7189Z" fill="#4769D8"/><path d="M68 19.859H40.2682L47.8796 36.2055H68V19.859Z" fill="#4769D8"/></svg></div>'),
        (this.atIndex = -1),
        (this.channels = { tabs: Backbone.Radio.channel("tabs"), templates: Backbone.Radio.channel("templates") }),
        (this.updateBlocksView = function () {
            m.setFilter("category", "", !0), m.setFilter("text", "", !0), m.setFilter("pro", "", !0), m.getModal().showTemplatesView(d);
        }),
        (this.setFilter = function (e, t, i) {
            m.channels.templates.reply("filter:" + e, t), i || m.channels.templates.trigger("filter:change");
        }),
        (this.getFilter = function (e) {
            return m.channels.templates.request("filter:" + e);
        }),
        (this.getFilterTerms = function () {
            return {
                category: {
                    callback: function (e) {
                        return _.any(this.get("category"), function (t) {
                            return t.indexOf(e) >= 0;
                        });
                    },
                },
                text: {
                    callback: function (e) {
                        return (
                            (e = e.toLowerCase()),
                            this.get("title").toLowerCase().indexOf(e) >= 0 ||
                            _.any(this.get("category"), function (t) {
                                return t.indexOf(e) >= 0;
                            })
                        );
                    },
                },
                type: {
                    callback: function (e) {
                        return this.get("type") === e;
                    },
                },
                pro: {
                    callback: function (e) {
                        return "pro" === e ? !!this.get("isPro") : !this.get("isPro");
                    },
                },
            };
        }),
        (this.showModal = function () {
            m.getModal().showModal(), m.showTemplatesView();
        }),
        (this.closeModal = function () {
            this.getModal().hideModal();
        }),
        (this.getModal = function () {
            return l || (l = new i.Modal()), l;
        }),
        (this.init = function () {
            m.setFilter("type", "section", !0), t.on("preview:loaded", o.bind(this));
        }),
        (this.getTabs = function () {
            var e = this.getFilter("type");
            return (
                (tabs = { section: { title: "Sections" }, /*headers: { title: "Headers" }, footers: { title: "Footers" }*/ }),
                _.each(tabs, function (t, i) {
                    e === i && (tabs[e].active = !0);
                }),
                { tabs: tabs }
            );
        }),
        (this.getCategory = function () {
            return s;
        }),
        (this.getTypeCategory = function () {
            var e = m.getFilter("type");
            return src[e];
        }),
        (this.showTemplatesView = function () {
            m.setFilter("category", "", !0),
                m.setFilter("text", "", !0),
                m.setFilter("pro", "", !0),
                d
                    ? m.getModal().showTemplatesView(d)
                    : m.loadTemplates(function () {
                          m.getModal().showTemplatesView(d);
                      });
        }),
        (this.showPreviewView = function (e) {
            m.getModal().showPreviewView(e);
        }),
        (this.loadTemplates = function (e) {
            m.requestLibraryData({
                onBeforeUpdate: m.getModal().showLoadingView.bind(m.getModal()),
                onUpdate: function () {
                    m.getModal().hideLoadingView(), e && e();
                },
            });
        }),
        (this.requestLibraryData = function (e) {
            if (d && !e.forceUpdate) return void(e.onUpdate && e.onUpdate());
            e.onBeforeUpdate && e.onBeforeUpdate();
            var t = {
                data: {},
                success: function (t) {
                    (d = new i.LibraryCollections.Template(t.templates)), 
                    t.category && (s = t.category),
                    t.type_category && (src = t.type_category),
                    e.onUpdate && e.onUpdate();
                },
            };
            e.forceSync && (t.data.sync = !0), elementorCommon.ajax.addRequest("aafe_get_template_library_data", t);
        }),
        (this.requestTemplateData = function (e, t) {
            var i = {
                unique_id: e,
                data: {
                    edit_mode: !0,
                    display: !0,
                    template_id: e
                }
            };
            t && jQuery.extend(!0, i, t), elementorCommon.ajax.addRequest("aafe_get_template_item_data", i);
        }),
        (this.insertTemplate = function (e) {
            var t = e.model,
                i = this;
            i.getModal().showLoadingView(),
                i.requestTemplateData(t.get("template_id"), {
                    success: function (e) {
                        i.getModal().hideLoadingView(), i.getModal().hideModal();
                        var a = {}; -
                        1 !== i.atIndex && (a.at = i.atIndex), $e.run("document/elements/import", {
                            model: t,
                            data: e,
                            options: a
                        }), (i.atIndex = -1);
                    },
                    error: function (e) {
                        i.showErrorDialog(e);
                    },
                    complete: function (e) {
                        i.getModal().hideLoadingView(),
                        window.elementor.$previewContents.find(".elementor-add-section .elementor-add-section-close").click();
                    },
                });
        }),
        (this.showErrorDialog = function (e) {
            if ("object" == typeof e) {
                var t = "";
                _.each(e, function (e) {
                        t += "<div>" + e.message + ".</div>";
                    }),
                    (e = t);
            } else e ? (e += ".") : (e = "<i>&#60;The error message is empty&#62;</i>");
            m.getErrorDialog()
                .setMessage('The following error(s) occurred while processing the request:<div id="elementor-template-library-error-info">' + e + "</div>")
                .show();
        }),
        (this.getErrorDialog = function () {
            return c || (c = elementorCommon.dialogsLibraryManager.createWidget("alert", {
                id: "elementor-template-library-error-dialog",
                headerMessage: "An error occurred"
            })), c;
        });
    }),
    (window.aafe.library = new i.LibraryManager()),
    window.aafe.library.init();

})(jQuery, window.elementor);