<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 *
 * @return array<string, array{    // Import name as key, description of the imported file as value
 *     path: string,               // Logical, relative or absolute path to the file
 *     type?: 'js'|'css'|'json',   // Type of the file, defaults to 'js'
 *     entrypoint?: bool,          // Whether the file is an entrypoint, for 'js' only
 * }|array{
 *     version: string,            // Version of the remote package
 *     package_specifier?: string, // Remote "package-name/path" specifier, defaults to the import name
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [
    'app' => ['path' => './assets/app.js', 'entrypoint' => true],
    '@hotwired/stimulus' => ['version' => '3.2.2'],
    '@symfony/stimulus-bundle' => ['path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js'],
    '@hotwired/turbo' => ['version' => '8.0.23'],
    '@tailwindplus/elements' => ['version' => '1.0.22'],
    '@symfony/ux-react' => ['path' => './vendor/symfony/ux-react/assets/dist/loader.js'],
    'react' => ['version' => '18.2.0'],
    'react-dom' => ['version' => '18.2.0'],
    'react-dom/client' => ['version' => '18.2.0'],
    'scheduler' => ['version' => '0.23.2'],
    'react/jsx-runtime' => ['version' => '18.2.0'],
    'react-big-calendar' => ['version' => '1.20.0'],
    'date-fns' => ['version' => '4.4.0'],
    '@babel/runtime/helpers/extends' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/defineProperty' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/objectWithoutProperties' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/typeof' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/classCallCheck' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/createClass' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/possibleConstructorReturn' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/getPrototypeOf' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/inherits' => ['version' => '7.29.7'],
    '@babel/runtime/helpers/slicedToArray' => ['version' => '7.29.7'],
    'clsx' => ['version' => '2.1.1'],
    'prop-types' => ['version' => '15.8.1'],
    'uncontrollable' => ['version' => '7.2.1'],
    'invariant' => ['version' => '2.2.4'],
    'date-arithmetic' => ['version' => '4.1.0'],
    '@babel/runtime/helpers/toConsumableArray' => ['version' => '7.29.7'],
    'lodash/chunk' => ['version' => '4.18.1'],
    'dom-helpers/position' => ['version' => '6.0.1'],
    'dom-helpers/animationFrame' => ['version' => '6.0.1'],
    'react-overlays' => ['version' => '5.2.1'],
    'dom-helpers/offset' => ['version' => '6.0.1'],
    'lodash/isEqual' => ['version' => '4.18.1'],
    'dom-helpers/height' => ['version' => '6.0.1'],
    'dom-helpers/querySelectorAll' => ['version' => '6.0.1'],
    'dom-helpers/contains' => ['version' => '6.0.1'],
    'dom-helpers/closest' => ['version' => '6.0.1'],
    'dom-helpers/listen' => ['version' => '6.0.1'],
    'lodash/findIndex' => ['version' => '4.18.1'],
    'lodash/range' => ['version' => '4.18.1'],
    'memoize-one' => ['version' => '6.0.0'],
    'dom-helpers/width' => ['version' => '6.0.1'],
    'lodash/sortBy' => ['version' => '4.18.1'],
    'dom-helpers/scrollbarSize' => ['version' => '6.0.1'],
    '@babel/runtime/helpers/toArray' => ['version' => '7.29.7'],
    'dom-helpers/addClass' => ['version' => '6.0.1'],
    'dom-helpers/removeClass' => ['version' => '6.0.1'],
    'lodash/defaults' => ['version' => '4.18.1'],
    'lodash/mapValues' => ['version' => '4.18.1'],
    'lodash/omit' => ['version' => '4.18.1'],
    'lodash/transform' => ['version' => '4.18.1'],
    'dayjs/plugin/isBetween' => ['version' => '1.11.21'],
    'dayjs/plugin/isSameOrAfter' => ['version' => '1.11.21'],
    'dayjs/plugin/isSameOrBefore' => ['version' => '1.11.21'],
    'dayjs/plugin/localeData' => ['version' => '1.11.21'],
    'dayjs/plugin/localizedFormat' => ['version' => '1.11.21'],
    'dayjs/plugin/minMax' => ['version' => '1.11.21'],
    'dayjs/plugin/utc' => ['version' => '1.11.21'],
    'dayjs/plugin/isLeapYear' => ['version' => '1.11.21'],
    'react-big-calendar/lib/css/react-big-calendar.min.css' => ['version' => '1.20.0', 'type' => 'css'],
    '@babel/runtime/helpers/esm/extends' => ['version' => '7.19.0'],
    '@babel/runtime/helpers/esm/objectWithoutPropertiesLoose' => ['version' => '7.19.0'],
    '@babel/runtime/helpers/esm/inheritsLoose' => ['version' => '7.12.5'],
    'react-lifecycles-compat' => ['version' => '3.0.4'],
    'dom-helpers/matches' => ['version' => '5.2.1'],
    'dom-helpers/addEventListener' => ['version' => '5.2.1'],
    '@restart/hooks/usePrevious' => ['version' => '0.4.7'],
    '@restart/hooks/useForceUpdate' => ['version' => '0.4.7'],
    '@restart/hooks/useGlobalListener' => ['version' => '0.4.7'],
    '@restart/hooks/useEventCallback' => ['version' => '0.4.7'],
    '@restart/hooks/useCallbackRef' => ['version' => '0.4.7'],
    '@restart/hooks/useSafeState' => ['version' => '0.4.7'],
    '@popperjs/core/lib/modifiers/arrow' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/computeStyles' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/eventListeners' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/flip' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/hide' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/offset' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/popperOffsets' => ['version' => '2.11.6'],
    '@popperjs/core/lib/modifiers/preventOverflow' => ['version' => '2.11.6'],
    '@popperjs/core/lib/enums' => ['version' => '2.11.6'],
    '@popperjs/core/lib/popper-base' => ['version' => '2.11.6'],
    'warning' => ['version' => '4.0.3'],
    'dom-helpers/ownerDocument' => ['version' => '5.2.1'],
    'dom-helpers/activeElement' => ['version' => '5.2.1'],
    'dom-helpers/canUseDOM' => ['version' => '5.2.1'],
    '@restart/hooks/useMounted' => ['version' => '0.4.7'],
    '@restart/hooks/useWillUnmount' => ['version' => '0.4.7'],
    'dom-helpers/css' => ['version' => '5.2.1'],
    'dom-helpers/isWindow' => ['version' => '5.2.1'],
    '@restart/hooks/useMergedRefs' => ['version' => '0.4.7'],
    'date-fns/locale/en-GB' => ['version' => '4.4.0'],
];
