import view from './view';
import edit from './edit';
import metadata from './block.json';
import { useBlockProps } from '@wordpress/block-editor';
import { editorHelp as icon } from '@wordpress/icons';
import { useState } from '@wordpress/element';

const { name } = metadata;

export { metadata, name };

export const settings = {
    icon: icon,
    edit: (props) => {
        const [focusOnLast, setFocusOnLast] = useState(false);
        const blockProps = useBlockProps();
        const metaWithIcon = { icon: icon, ...metadata };
        let content = null;
        if (props.isSelected) {
            content = edit({
                metadata: metaWithIcon,
                focusOnLast,
                setFocusOnLast,
                ...props
            });
        } else {
            content = view({ metadata: metaWithIcon, ...props });
        }
        return <div {...blockProps}>{content}</div>;
    },
    save: () => null
};
