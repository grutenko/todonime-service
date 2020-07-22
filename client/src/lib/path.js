
export function cdn(path) {
    return (process.env.REACT_APP_CDN_BASE || 'https://cdn.todonime.ru') + path;
}