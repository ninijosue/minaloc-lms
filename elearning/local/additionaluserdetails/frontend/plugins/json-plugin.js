export default function jsonPlugin() {
  return {
    name: 'json-plugin',
    transform(code, id) {
      if (!id.endsWith('.json')) return null;

      try {
        const parsed = JSON.parse(code);
        const exportCode = `export default ${JSON.stringify(parsed, null, 2)};`;
        return {
          code: exportCode,
          map: null // No source map needed for JSON
        };
      } catch (error) {
        this.error(`Failed to parse JSON in ${id}: ${error.message}`);
      }
    }
  };
}