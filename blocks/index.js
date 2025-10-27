import { registerBlockType } from '@wordpress/blocks';
import * as Faq from './Faq';

function init(block) {
  const name = block.name;
  const metadata = block.metadata;
  const configuration = { name, ...metadata };
  return registerBlockType(configuration, block.settings);
}

init(Faq);
