module.exports = {
  plugins: {
    'postcss-import': {},
    'autoprefixer': {},
    'cssnano': process.env.NODE_ENV === 'production' ? {} : false,
  },
};