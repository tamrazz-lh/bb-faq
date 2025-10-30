import view from './view';
import edit from './edit';
import metadata from './block.json';
import { helpFilled as icon } from '@wordpress/icons';

import './assets/editor.scss';

const { name } = metadata;

export { metadata, name };

export const settings = {
  icon: icon,
  edit: edit,
  save: view
};
