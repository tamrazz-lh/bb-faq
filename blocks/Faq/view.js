import { Placeholder } from '@wordpress/components';
import styles from './assets/view.module.scss';

export default ({ attributes, metadata }) => {
    const items = (attributes.items || []).map((item, idx) => (
        <li key={idx}>
            <strong>{item.question}</strong>
            <div dangerouslySetInnerHTML={{ __html: item.answer }} />
        </li>
    ));
    return (
        <Placeholder icon={metadata.icon} label={metadata.title} className={styles.placeholder}>
            <ul>{items}</ul>
        </Placeholder>
    );
};
