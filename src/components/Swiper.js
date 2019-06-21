/* eslint-disable */

import React, { Component, Children, cloneElement, createRef, Fragment } from 'react';
import arrayEqual from 'array-equal';
import deepEqual from 'fast-deep-equal';
import omit from 'lodash.omit';
import pick from 'lodash.pick';
import { pickBy } from 'lodash';

import classnames from 'classnames';

const $themes = Symbol('themes');
const $theme = Symbol('theme');
const $instances = Symbol('instances');

export default class Swiper extends Component {
  ref = createRef()
  swiper = null
  initialized = false

  static [$themes] = new Map()
  static [$instances] = new WeakMap()

  static get themes() {
    console.log('get themes...', this[$themes]);
    return this[$themes];
  }

  static get theme() {
    return this[$theme];
  }

  static set theme(theme) {
    console.log('SET THEME: ', theme);
    this[$theme] = theme;
  }

  static getInstance(el) {
    return this[$instances].get(el);
  }

  static defaultProps = {
    className: null,
    selector: null,
    wrapperSelector: null,
    theme: null,
    wrapperClass: 'swiper-wrapper',
    slideClass: 'swiper-slide',
    initialSlide: 0,
    navigation: true,
    pagination: 'bullets',
    scrollbar: false,
    slidesPerView: 1,
    spaceBetween: 0,
    simulateTouch: true,
    noSwipingSelector: '*[data-block]',
    watchSlidesVisibility: true,
    watchSlidesProgress: false,
    autoplay: null,
    loop: false,
    thumbs: null
  }

  static events = [
    'transitionStart',
    'transitionEnd'
  ];

  static getOptions = (props) => {
    const options = omit(pick(props, Object.keys(Swiper.defaultProps)), [
      'className',
      'selector',
      'wrapperSelector',
      'theme',
      'onInit',
      'onDestroy'
    ]);

    let result = {
      ...options,
      slidesPerView: options.slidesPerView <= 0 ? 'auto' : options.slidesPerView ,
      navigation: options.navigation ? {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
        ...(typeof options.navigation === 'object' ? options.navigation : {}),
      } : undefined,
      pagination: options.pagination ? {
        el: '.swiper-pagination',
        clickable: true,
        type: typeof options.pagination === 'string' ? options.pagination : options.pagination.type || 'bullets',
        ...(typeof options.pagination === 'object' ? options.pagination : {}),
      } : undefined,
      scrollbar: options.scrollbar ? {
        el: '.swiper-scrollbar',
        draggable: true,
        ...(typeof options.scrollbar === 'object' ? options.scrollbar : {})
      } : undefined,
      autoplay: options.autoplay ? {
        delay: 3000,
        ...(typeof options.autoplay === 'object' ? options.autoplay : {})
      } : undefined,
      thumbs: options.thumbs ? {
        ...(typeof options.thumbs === 'object' ? options.thumbs : {}),
      } : undefined,
    };

    result = pickBy(result, v => v !== null && v !== undefined);

    return result;
  }

  componentDidMount() {
    this.forceUpdate();
    window.requestAnimationFrame(() => {
      this.forceUpdate();
    });
  }

  componentDidUpdate(prevProps, prevState) {
    this.init(prevProps);
  }

  getSwiperElement() {
    const { selector } = this.props;
    const { current: element } = this.ref;
    const swiperElement = element && (selector && element.querySelector(selector)) || element;

    return swiperElement;
  }

  getHandle() {
    return {
      slideTo: (...args) => {
        requestAnimationFrame(() => {
          if (this.swiper) {
            this.swiper.slideTo(...args);
          }
        });
      }
    }
  }

  init(prevProps = {}) {
    const { selector, className, wrapperSelector, onInit, ...props } = this.props;

    const compareProps = omit(this.props, [ 'onInit' ]);
    const comparePrevProps = omit(prevProps, [ 'onInit' ]);

    const options = Swiper.getOptions(props);
    const swiperElement = this.getSwiperElement();

    if (!swiperElement) {
      return;
    }

    /*
    if (this.swiper && deepEqual(compareProps, comparePrevProps)) {
      return;
    }
    */

    if (wrapperSelector) {
      const wrapperElement = swiperElement.querySelector(wrapperSelector);

      if (wrapperElement) {
        wrapperElement.classList.add(options.wrapperClass);
      }
    }

    const activeIndex = this.swiper ? this.swiper.activeIndex : -1;

    if (this.swiper) {
      this.swiper.destroy(false);
    }

    swiperElement.classList.add('swiper-container');

    if (className) {
      swiperElement.classList.add(className);
    }

    const events = Object.assign({}, ...Swiper.events.map((name) => ({
      [name]: (...args) => {
        const handle = this.props[`on${name.charAt(0).toUpperCase() + name.substring(1)}`];

        if (handle) {
          handle(...args);
        }
      }
    })));


    if (options.thumbs) {
      if (typeof options.thumbs.swiper === 'string') {
        options.thumbs = ((selector) => ({
          ...options.thumbs,
          get swiper() {
            const el = document.querySelector(selector);
            const instance = Swiper[$instances].get(el);

            return instance;
          }
        }))(options.thumbs.swiper);
      }
    }

    this.swiper = new global.Swiper(swiperElement, {
      ...options,
      initialSlide: activeIndex >= 0 ? activeIndex : this.props.initialSlide,
      // on: events
      on: {
        transitionStart: () => {
          this.getSwiperElement().classList.add('is-animating');
        },
        transitionEnd: () => {
          this.getSwiperElement().classList.remove('is-animating');
        },
      }
    });

    Swiper[$instances].set(swiperElement, this.swiper);

    for (let [ event, func ] of Object.entries(events)) {
      this.swiper.on(event, func);
    }

    if (!this.initialized) {
      this.initialized = true;
      onInit && onInit(this.getHandle());
    }
  }

  getNavigation() {
    const { navigation, pagination, scrollbar } = Swiper.getOptions(this.props);
    const theme = this.props.theme || Swiper.theme;
    const { classes = {} } = Swiper.themes.get(theme) || {};

    return (
      <Fragment>
        {navigation && (
          <Fragment>
            <div className={classnames('swiper-button-prev', classes['swiper-button-prev'])}></div>
            <div className={classnames('swiper-button-next', classes['swiper-button-next'])}></div>
          </Fragment>
        )}
        {pagination && (
          <div className={classnames('swiper-pagination', classes['swiper-pagination'])}></div>
        )}
        {scrollbar && (
          <div className={classnames('swiper-scrollbar', classes['swiper-scrollbar'])}></div>
        )}
      </Fragment>
    );
  }

  render() {
    const {
      id,
      className,
      selector,
      wrapperClass,
      slideClass,
      navigation,
      children,
      style
    } = this.props;

    const swiperElement = this.getSwiperElement();

    return (
      <div ref={this.ref} id={id} style={style}>
        {!selector ? (
          <Fragment>
            <div className={wrapperClass}>
              {Children.map(children, child => (
               // <div className={slideClass}>
                cloneElement(child, {
                  className: classnames(child.props.className, slideClass)
                })
               // </div>
              ))}
            </div>
            {this.getNavigation()}
          </Fragment>
        ) : (
          <Fragment>
            {children}
            {swiperElement && ReactDOM.createPortal(this.getNavigation(), swiperElement)}
          </Fragment>
        )}
      </div>
    );
  }

  componentWillUnmount() {
    const { onDestroy } = this.props;

    if (this.swiper) {
      this.swiper.destroy(false);

      onDestroy && onDestroy();
    }
  }
}
