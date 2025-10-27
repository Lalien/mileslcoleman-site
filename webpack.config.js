const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
    entry: './src/index.ts',
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'main.js',
        clean: true,
    },
    mode: 'development',
    plugins: [
        new HtmlWebpackPlugin({
            template: './index.html',
        }),
        new CopyWebpackPlugin({
            patterns: [
                {
                from: path.resolve(__dirname, 'static'),
                to: path.resolve(__dirname, 'dist/static') // or just 'static' if relative to dist
                }
            ]
        }),
        new MiniCssExtractPlugin({
            filename: "[name].[contentHash].css"
        })
    ],
    resolve: {
        extensions: ['.ts', '.js', '.jsx'], // allow .ts imports
    },
    devServer: {
        static: './dist',
        open: true,
        hot: true
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader'],
            },
            {
                test: /\.ts$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.(js|jsx)$/,   // handle .js and .jsx files
                exclude: /node_modules/,
                use: {
                loader: 'babel-loader'
                }
            }
        ],
    },
};  