import { registerBlockType } from '@wordpress/blocks';
import * as FaqBlock from "./Faq";

function init(block) {
    const name = block.name;
    const metadata = block.metadata;
    const Configuration = { name, ...metadata };
    return registerBlockType(Configuration, block.settings);
}

init(FaqBlock);
