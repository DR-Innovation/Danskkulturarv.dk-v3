(function (wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var { PanelBody, TextControl, SelectControl, ExternalLink, ServerSideRender } = wp.components;
    var { useEffect } = wp.element;

    var feedOptions = [{ label: '--- Select a Saved Feed ---', value: '' }];
    if (window.fbWidgetData && window.fbWidgetData.savedFeeds) {
        window.fbWidgetData.savedFeeds.forEach(function (feed, index) {
            feedOptions.push({ label: feed.name, value: index.toString() });
        });
    }

    registerBlockType('social-feeds-for-wordpress/facebook-page-like', {
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps();

            // This effect runs whenever the selected configuration changes
            useEffect(function () {
                // 1. Find the Gutenberg Editor Canvas iframe
                const editorCanvas = document.querySelector('iframe[name="editor-canvas"]');

                // 2. Determine which context to use
                const context = editorCanvas ? editorCanvas.contentWindow : window;
                const doc = editorCanvas ? editorCanvas.contentDocument : document;

                // 3. Logic to trigger the parse
                const triggerFBParse = () => {
                    if (context && context.FB) {
                        // Find the block specifically inside the iframe document
                        const blockElement = doc.getElementById(blockProps.id);
                        const n = blockElement.getElementsByClassName("fb-shortcode-container")[0];
                        const loader = blockElement.getElementsByClassName("fb_loader")[0];
                        if (blockElement) {
                            // IMPORTANT: Target the .fb-page div specifically inside your block
                            const fbPage = blockElement.querySelector('.fb-page');
                            if (fbPage) {
                                if (loader) {
                                    loader.style.display = "none";
                                }
                                context.FB.XFBML.parse(n);
                            }
                        }
                    }
                };

                // 4. Use a slightly longer timeout or an interval to ensure ServerSideRender is done
                const timer = setTimeout(triggerFBParse, 2000);

                return () => clearTimeout(timer);
            }, [attributes.selectedFeedIndex, attributes.title]);

            var settingsUrl = window.fbWidgetData ? window.fbWidgetData.settingsUrl : '#';

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: 'Widget Settings', initialOpen: true },
                        el(TextControl, {
                            label: 'Display Title',
                            value: attributes.title,
                            onChange: function (val) { setAttributes({ title: val }); }
                        }),
                        el(SelectControl, {
                            label: 'Choose Configuration',
                            value: attributes.selectedFeedIndex,
                            options: feedOptions,
                            onChange: function (val) { setAttributes({ selectedFeedIndex: val }); }
                        }),
                        el('div', { style: { marginTop: '15px', paddingTop: '15px', borderTop: '1px solid #ddd' } },
                            el(ExternalLink, { href: settingsUrl }, 'Manage or Add New Feeds')
                        )
                    )
                ),

                // Live Editor Canvas using ServerSideRender to use your render.php logic
                el('div', { ...blockProps, key: 'editor' },
                    el(wp.serverSideRender, {
                        block: 'social-feeds-for-wordpress/facebook-page-like',
                        attributes: attributes
                    })
                )
            ];
        }
    });
})(window.wp);
